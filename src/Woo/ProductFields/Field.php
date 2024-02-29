<?php

namespace WPPluginFramework\Woo\ProductFields;

use WPPluginFramework\{
    Logger,
    WPFObject,
};
use WPPluginFramework\Events\{
    ILoadEvent,
    IDisableEvent,
    IUninstallEvent,
};
use WPPluginFramework\Traits\RequiresWooCommerce;

use function WPPluginFramework\strsuffix;

abstract class Field extends WPFObject implements
    ILoadEvent,
    IDisableEvent,
    IUninstallEvent
{
    use RequiresWooCommerce;

    #region Protected Properties

    protected ?string $id = null;
    protected ?string $label = null;
    protected ?string $description = null;
    protected bool $description_as_tip = true;

    protected string $target = FieldTarget::DEFAULT;

    protected string $class = 'short';
    protected ?string $wrapper_class = null;
    protected ?string $style = null;

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
     * Run when metadata is processed. Save the data here.
     * @param $post_id The post's id. Provided by WC.
     * @return void
     */
    public function onProcessMeta($post_id): void
    {
        Logger::debug('onProcessMeta()', get_class(), get_called_class());
        $value = isset($_POST[$this->getID()]) ? $_POST[$this->getID()] : null;
        $value = $this->sanitizeValue($value);
        update_post_meta($post_id, $this->getID(), $value);
    }

    /**
     * Run when the $target event is triggered.
     * Invoke the function to draw the field.
     * @return void
     */
    public function onShowMetaBox(): void
    {
        Logger::debug('onShowMetaBox()', get_class(), get_called_class());
        $this->drawMetaBox();
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

    /**
     * Draw the meta box field.
     * @return void
     */
    protected function drawMetaBox(): void
    {
        echo('<p class="form-field ' . esc_attr($this->getId()) . '_field">');
        echo('<label for="' . $this->getId() . '">' . $this->getLabel() . '</label>');

        if ($this->description_as_tip) {
            echo(wc_help_tip($this->getDescription()));
        }

        echo('<input type="text" disabled="disabled" class="short" placeholder="' . $this->getId() . '"></input>');

        if (!$this->description_as_tip) {
            echo('<span class="description">' . $this->getDescription() . '</span>');
        }

        echo('</p>');
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
        delete_metadata('post', 0, $this->getID(), '', true);
    }

    #endregion
    #region ILoadEvent Implementation
    public function onLoadEvent(): void
    {
        // Hook display of field
        add_action($this->target, [$this, 'onShowMetaBox']);

        // Hook saving of field
        add_action('woocommerce_process_product_meta', [$this, 'onProcessMeta']);
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
