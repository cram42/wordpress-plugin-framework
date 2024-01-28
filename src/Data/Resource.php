<?php

namespace WPPluginFramework\Data;

use WPPluginFramework\{
    Logger,
    WPFObject,
};

use WPPluginFramework\Data\Attributes\{
    IResourceFieldAttribute,
    ResourceField,
    AutoIncrement,
    Required,
    PrimaryKey,
};

use function WPPluginFramework\{
    getPropertiesSorted,
    strsuffix,
    typePHPtoREST,
};

abstract class Resource extends WPFObject
{
    #region Public Properties

    #[AutoIncrement, PrimaryKey, Required]
    public int $id = 0;

    #endregion
    #region Protected Properties

    protected string $permission_read = 'read';
    protected string $permission_write = 'edit_others_posts';
    protected string|null $rest_endpoint = null;

    #endregion
    #region Private Properties

    private array|null $resource_fields = null;

    #endregion
    #region Public Methods

    public function canGetAll(): bool
    {
        return $this->canRead();
    }

    public function canCreate(array $data): bool
    {
        return $this->canWrite();
    }

    public function canGet(int $id): bool
    {
        return $this->canRead();
    }

    public function canEdit(int $id, array $data): bool
    {
        return $this->canWrite();
    }

    public function canDelete(int $id): bool
    {
        return $this->canWrite();
    }

    /**
     * Get or generate the fields from this resource.
     * Returns a numeric array as:
     * [
     *     0 => [
     *         'property_name' => property_name,
     *         'field_name     => field_name,
     *         'type'          => property_type,
     *         'attributes'    => array_of_attribute_class_instances,
     *     ]
     * ]
     * @return array
     */
    public function getResourceFields(): array
    {
        if (!$this->resource_fields) {
            $this->resource_fields = [];

            $reflector = new \ReflectionClass($this);
            $properties = getPropertiesSorted($reflector);

            foreach ($properties as $property) {
                $attributes = $property->getAttributes(
                    IResourceFieldAttribute::class,
                    \ReflectionAttribute::IS_INSTANCEOF
                );

                if (count($attributes) > 0) {
                    $property_name = $property->getName();
                    $field_name = $property_name;
                    $field_attributes = [];

                    foreach ($attributes as $attribute) {
                        $instance = $attribute->newInstance();
                        if ($instance instanceof ResourceField) {
                            if (!empty($instance->name)) {
                                $field_name = $instance->name;
                            }
                        }
                        $field_attributes[] = $instance;
                    }

                    $this->resource_fields[] = [
                        'property_name' => $property_name,
                        'field_name'    => $field_name,
                        'type'          => $property->getType()->getName(),
                        'attributes'    => $field_attributes,
                    ];
                }
            }

        }
        return $this->resource_fields;
    }

    /**
     * Reformat resource fields into args compatible with register_rest_route.
     * @param bool $include_id Keep the 'id' field in the args?
     * @return array
     */
    public function getRESTArgs(bool $include_id = false): array
    {
        $args = [];

        foreach ($this->getResourceFields() as $field) {
            
            if (!$include_id && ($field['property_name'] == 'id')) {
                continue;
            }

            $arg_data = [
                'type' => typePHPtoREST($field['type']),
            ];

            foreach ($field['attributes'] as $field_attribute) {
                if ($field_attribute instanceof Required) {
                    $arg_data['required'] = true;
                }
            }

            $args[$field['field_name']] = $arg_data;
        }

        return $args;
    }

    /**
     * Get or generate the REST endpoint for this resource.
     * @return string
     */
    public function getRESTEndpoint(): string
    {
        if (!$this->rest_endpoint) {
            $temp = get_called_class();                                  // My\Namespace\MyAwesomeItemsResource
            $temp = strsuffix($temp, '\\');                              // MyAwesomeItemsResource
            $temp = $this->trimClassSuffixes($temp);                     // MyAwesomeItems
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $temp); // My-Awesome-Items
            $temp = strtolower($temp);                                   // my-awesome-items
            $this->rest_endpoint = $temp;
        }
        return $this->rest_endpoint;
    }

    #endregion
    #region Protected Methods

    /**
     * Can the current user read this record type?
     * @return bool
     */
    protected function canRead(): bool
    {
        return current_user_can($this->permission_read);
    }

    /**
     * Can the current user write this record type?
     * @return bool
     */
    protected function canWrite(): bool
    {
        return current_user_can($this->permission_write);
    }

    /**
     * Trim class suffixes from name.
     * @return string
     */
    protected function trimClassSuffixes(string $name): string
    {
        return preg_replace('/Resource$/', '', $name);
    }

    #endregion
    #region Abstract Methods

    abstract protected function getAll(): array;
    abstract protected function create(array $record): array;
    abstract protected function get(int $id): array|null;
    abstract protected function edit(int $id, array $data): array|null;
    abstract protected function delete(int $id): bool;

    #endregion
}
