<?php

namespace WPPluginFramework\Users\Fields;

use WPPluginFramework\{
    Logger, LogLevel,
    WPFObject,
};

use function WPPluginFramework\strsuffix;

Logger::setLevel(__NAMESPACE__ . '\Field', LogLevel::DEBUG);

abstract class Field extends WPFObject
{
    #region Protected Properties

    protected ?string $id = null;
    protected ?string $label = null;
    protected ?string $description = null;

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
     * Draw the field content.
     * @param $user_id The user's id.
     * @return void
     */
    public function drawField($user_id): void
    {
        Logger::debug('drawField()', get_class(), get_called_class());
        echo('<input type="text" disabled="disabled" class="regular-text" placeholder="' . $this->getId() . '"></input>');
        echo('<p class="description">' . $this->getDescription() . '</p>');
    }

    /**
     * Save the data here.
     * @param $user_id The user's id.
     * @return void
     */
    public function saveField($user_id): void
    {
        Logger::debug('saveField()', get_class(), get_called_class());
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
}
