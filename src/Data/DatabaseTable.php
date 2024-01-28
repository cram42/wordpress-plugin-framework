<?php

namespace WPPluginFramework\Data;

use WPPluginFramework\{
    Logger
};

use function WPPluginFramework\{
    typePHPtoMySQL
};

use WPPluginFramework\Data\Attributes\{
    IMySQLFieldFlag,
    ResourceField,
    Length,
    MySQLConstraint,
};

use WPPluginFramework\Events\{
    IEnableEvent,
    IDisableEvent,
    IUninstallEvent,
};

class DatabaseTable extends Resource implements IEnableEvent, IDisableEvent, IUninstallEvent
{
    #region Protected Properties

    protected array $default_items = [];
    protected bool $drop_on_disable = false;
    protected bool $drop_on_uninstall = true;
    protected string|null $table_name = null;

    #endregion
    #region Private Properties

    private string|null $table_definition = null;

    #endregion
    #region Public Methods

    /**
     * Get table definition for MySQL.
     * @return array
     */
    public function getTableDefinition(): string
    {
        if (!$this->table_definition) {
            global $wpdb;

            $columns = [];
            $constraints = [];

            $fields = $this->getResourceFields();
            foreach ($fields as $field) {
                $column_name = $field['field_name'];
                $column_type = typePHPtoMySQL($field['type']);
                $column_flags = [];

                foreach ($field['attributes'] as $attribute) {
                    if ($attribute instanceof IMySQLFieldFlag) {
                        $column_flags[] = $attribute->getFlagString();
                    }
                    if ($attribute instanceof MySQLConstraint) {
                        $constraint_name = empty($attribute->name) ? $column_name : $attribute->name;
                        $constraint_class = get_class($attribute);
                        $constraints[$constraint_name]['instance'] = $attribute;
                        $constraints[$constraint_name]['fields'][] = $column_name;
                    }
                }

                $columns[] = sprintf(
                    '%s %s%s',
                    $column_name,
                    $column_type,
                    empty($column_flags) ? '' : ' ' . implode(' ', $column_flags)
                );
            }

            $constraints_new = [];
            foreach ($constraints as $constraint_name => $constraint) {
                $constraints_new[] = $constraint['instance']->getConstraintString(
                    $constraint_name,
                    $constraint['fields']
                );
            }

            $this->table_definition = sprintf(
                <<<EOF
                CREATE TABLE %s (
                    %s
                ) %s;
                EOF,
                $this->getTableName(),
                implode(",\n    ", array_merge($columns, $constraints_new)),
                $wpdb->get_charset_collate()
            );
        }

        return $this->table_definition;
    }

    /**
     * Get or generate the table name.
     * @return string
     */
    public function getTableName(): string
    {
        global $wpdb;
        if (!$this->table_name) {
            $temp = get_called_class();                 // My\Namespace\AwesomeItemsTable
            $temp = $this->trimClassSuffixes($temp);    // My\Namespace\AwesomeItems
            $temp = str_replace('\\', '_', $temp);      // My_Namespace_AwesomeItems
            $temp = strtolower($temp);                  // my_namespace_awesomeitems
            $this->table_name = $temp;
        }
        return $wpdb->prefix . $this->table_name;       // wp_my_namespace_awesomeitems
    }

    #endregion
    #region Protected Methods

    /**
     * Create a new MySQL database table for this resource.
     * @return void
     */
    protected function createTable(): void
    {
        $query = $this->getTableDefinition();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($query);
    }

    /**
     * Drop the MySQL database table for this resource.
     * @return void
     */
    protected function dropTable(): void
    {
        global $wpdb;
        $query = sprintf('DROP TABLE IF EXISTS %s', $this->getTableName());
        $wpdb->query($query);
    }

    /**
     * Populate the MySQL database table with default data.
     * @return void
     */
    protected function populateTable(): void
    {
        foreach ($this->default_items as $item) {
            $this->create($item);
        }
    }

    /**
     * Trim class suffixes from name.
     * @return string
     */
    #[Override]
    protected function trimClassSuffixes(string $name): string
    {
        $name = parent::trimClassSuffixes($name);
        return preg_replace('/Table$/', '', $name);
    }

    protected function parseResult(array|null $input): array|null
    {
        if ($input === null) {
            return null;
        }

        $fields = $this->getResourceFields();
        $output = [];

        foreach ($fields as $field) {
            $field_name = $field['field_name'];
            $property_name = $field['property_name'];
            $output_value = null;

            if (array_key_exists($field_name, $input)) {
                if ($input[$field_name] !== null) {
                    $output_value = $input[$field_name];
                    settype($output_value, $field['type']);
                }
            }

            $output[$property_name] = $output_value;
        }

        return $output;
    }

    #endregion
    #region Resource De-abstraction

    public function getAll(): array
    {
        global $wpdb;
        $query = sprintf('SELECT * FROM %s;', $this->getTableName());
        $results = $wpdb->get_results($query, ARRAY_A);
        return array_map(
            fn($result) => $this->parseResult($result),
            $results
        );
    }

    public function create(array $data): array
    {
        global $wpdb;
        $result = $wpdb->insert($this->getTableName(), $data);
        if ($result == 0) {
            $error = sprintf('create() failed. "%s"', $wpdb->last_error);
            Logger::error($error, get_class(), get_called_class());
            throw new \Exception($error);
        }
        return $this->get($wpdb->insert_id);
    }

    public function get(int $id, bool $unparsed = false): array|null
    {
        global $wpdb;
        $query = sprintf('SELECT * FROM %s WHERE id = %%s;', $this->getTableName());
        $query = $wpdb->prepare($query, $id);
        $result = $wpdb->get_row($query, ARRAY_A);
        return $unparsed ? $result : $this->parseResult($result);
    }

    public function edit(int $id, array $data): array|null
    {
        unset($data['id']);
        
        global $wpdb;
        $result = $wpdb->update(
            $this->getTableName(),
            $data,
            ['id' => $id]
        );
        if (false === $result) {
            $error = sprintf('edit() failed. "%s"', $wpdb->last_error);
            Logger::error($error, get_class(), get_called_class());
            throw new \Exception($error);
        }

        return $this->get($id);
    }

    public function delete(int $id): bool
    {
        global $wpdb;
        $result = $wpdb->delete(
            $this->getTableName(),
            ['id' => $id]
        );
        return $result > 0;
    }

    #endregion
    #region IEnableEvent Implementation

    public function onEnableEvent(): void
    {
        $this->createTable();
        $this->populateTable();
    }

    #endregion
    #region IDisableEvent Implementation

    public function onDisableEvent(): void
    {
        if ($this->drop_on_disable) {
            $this->dropTable();
        }
    }

    #endregion
    #region IUninstallEvent Implementation

    public function onUninstallEvent(): void
    {
        if ($this->drop_on_uninstall) {
            $this->dropTable();
        }
    }

    #endregion
}
