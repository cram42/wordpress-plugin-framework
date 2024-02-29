<?php

namespace WPPluginFramework\Users\Fields;

use WPPluginFramework\{
    Logger, LogLevel,
    WPFObject,
};
use WPPluginFramework\Events\{
    ILoadEvent,
    IDisableEvent,
    IUninstallEvent,
};

use function WPPluginFramework\strsuffix;

Logger::setLevel(__NAMESPACE__ . '\Field', LogLevel::DEBUG);

abstract class Field extends WPFObject implements
    ILoadEvent,
    IDisableEvent,
    IUninstallEvent
{
    #region Protected Properties

    protected ?string $id = null;
    protected ?string $label = null;
    protected bool $show_description = false;
    protected ?string $description = null;
    protected bool $disabled = false;

    protected bool $show_in_profile = true;
    protected ?string $profile_table = null;

    protected bool $wipe_on_disable = false;
    protected bool $wipe_on_uninstall = true;

    #endregion
    #region Public Methods

    /**
     * Get or generate the field description.
     * @return string
     */
    public function getDescription(): string
    {
        if (!$this->description) {
            $this->description = sprintf(
                'Added by %s',
                get_called_class()
            );
        }
        return $this->description;
    }

    /**
     * Get or generate the field ID.
     * @return string
     */
    public function getID(): string
    {
        if (!$this->id) {
            $temp = get_called_class();                     // My\Namespace\ProductInfoField
            $temp = preg_replace('/Field$/', '', $temp);    // My\Namespace\ProductInfo
            $temp = str_replace('\\', '_', $temp);          // My_Namespace_ProductInfo
            $temp = strtolower($temp);                      // my_namespace_productinfo
            $this->id = $temp;
        }
        return $this->id;
    }

    /**
     * Get or generate the field label.
     * @return string
     */
    public function getLabel(): string
    {
        if (!$this->label) {
            $temp = get_called_class();                                     // My\Namespace\ProductInfoField
            $temp = strsuffix($temp, '\\');                                 // ProductInfoField
            $temp = preg_replace('/Field$/', '', $temp);                    // ProductInfo
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Product Info
            $this->label = $temp;
        }
        return $this->label;
    }

    /**
     * Draw the field row for profile table.
     * @param $user_id The user's id.
     * @return void
     */
    public function onProfileTable($user_id): void
    {
        Logger::debug('onProfileTable()', get_class(), get_called_class());
        echo('<tr>');
        echo('<th><label for="' . $this->getID() . '">' . $this->getLabel() . '</label></th>');
        echo('<td>');
        echo('<input type="text" disabled="disabled" class="regular-text" placeholder="' . $this->getId() . '"></input>');
        if ($this->show_description) {
            echo('<p class="description">' . $this->getDescription() . '</p>');
        }
        echo('</td>');
        echo('</tr>');
    }

    /**
     * Run when profile is updated. Save the data here.
     * @param $user_id The user's id. Provided by WP.
     * @return void
     */
    public function onProfileUpdate($user_id): void
    {
        Logger::debug('onProfileUpdate()', get_class(), get_called_class());
        $value = $_POST[$this->getID()];
        $value = $this->sanitizeValue($value);
        update_user_meta($user_id, $this->getID(), $value);
    }

    #endregion
    #region Protected Methods

    /**
     * Sanitize value for storage.
     * @return mixed
     */
    protected function sanitizeValue(mixed $value): mixed
    {
        return $value;
    }

    #endregion
    #region Private Methods

    /**
     * Wipe any metadata created by this field.
     * @return void
     */
    private function wipeData(): void
    {
        Logger::info(
            sprintf('Wiping metadata id "%s".', $this->getID()),
            get_class(),
            get_called_class()
        );
        delete_metadata('user', 0, $this->getID(), '', true);
    }

    #endregion
    #region ILoadEvent Implementation
    public function onLoadEvent(): void
    {
        Logger::debug('onLoadEvent()', get_class(), get_called_class());

        if ($this->show_in_profile) {
            if ($this->profile_table != null) {
                add_action('wpf_profiletable_' . $this->profile_table, [$this, 'onProfileTable']);
            } else {
                Logger::error('profile_table is not set', get_class(), get_called_class());
            }

            add_action('personal_options_update', [$this, 'onProfileUpdate']);
            add_action('edit_user_profile_update', [$this, 'onProfileUpdate']);
        }
    }
    #endregion
    #region IDisableEvent Implementation
    public function onDisableEvent(): void
    {
        if ($this->wipe_on_disable) {
            $this->wipeData();
        }
    }
    #endregion
    #region IUninstallEvent Implementation
    public function onUninstallEvent(): void
    {
        if ($this->wipe_on_uninstall) {
            $this->wipeData();
        }
    }
    #endregion
}
