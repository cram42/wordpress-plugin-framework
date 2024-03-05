<?php

namespace WPPluginFramework\Woo\UserFields;

use WPPluginFramework\Logger;

use function WPPluginFramework\strsuffix;

abstract class TextField extends Field
{
    protected const TEMPLATE_MY_ACCOUNT = 'myaccount/text-field.php';
    protected const TEMPLATE_PROFILE = 'wp-admin/user-edit/text-field.php';
    protected const TEMPLATE_REGISTRATION = 'myaccount/form-login/text-field.php';

    #region Protected Properties

    protected ?string $placeholder = null;

    #endregion
    #region Public Methods

    /**
     * Get or generate the checkbox text.
     * @return string
     */
    public function getPlaceholder(): string
    {
        if (!$this->placeholder) {
            $temp = get_called_class();                                     // My\Namespace\CoolTextField
            $temp = strsuffix($temp, '\\');                                 // CoolTextField
            $temp = preg_replace('/Field$/', '', $temp);                    // CoolText
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Cool Text
            $this->placeholder = $temp;
        }
        return $this->placeholder;
    }

    #endregion
    #region Protected Methods

    /**
     * Sanitize value for storage.
     * @return mixed
     */
    #[Override]
    protected function sanitizeValue(mixed $value): mixed
    {
        return esc_attr($value);
    }

    #endregion
}
