<?php

namespace WPPluginFramework\Woo\UserFields;

use WPPluginFramework\{
    Logger, LogLevel,
};

use function WPPluginFramework\strsuffix;

Logger::setLevel(__NAMESPACE__ . '\CheckboxField', LogLevel::DEBUG);

abstract class CheckboxField extends Field
{
    protected const TEMPLATE_MY_ACCOUNT = 'myaccount/checkbox-field.php';
    protected const TEMPLATE_PROFILE = 'wp-admin/user-edit/checkbox-field.php';
    protected const TEMPLATE_REGISTRATION = 'myaccount/form-login/checkbox-field.php';

    #region Protected Properties

    protected ?string $text = null;

    #endregion
    #region Public Methods

    /**
     * Get or generate the checkbox text.
     * @return string
     */
    public function getText(): string
    {
        if (!$this->text) {
            $temp = get_called_class();                                     // My\Namespace\CoolCheckboxField
            $temp = strsuffix($temp, '\\');                                 // CoolCheckboxField
            $temp = preg_replace('/Field$/', '', $temp);                    // CoolCheckbox
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Cool Checkbox
            $this->text = $temp;
        }
        return $this->text;
    }

    #endregion
    #region Protected Methods

    /**
     * Get the value from $_POST.
     * @return mixed
     */
    #[Override]
    protected function getPostedValue(): mixed
    {
        return array_key_exists($this->getID(), $_POST);
    }

    /**
     * Get the saved value.
     * @param int $user_id
     * @return bool
     */
    #[Override]
    protected function getSavedValue(int $user_id): bool
    {
        $value = parent::getSavedValue($user_id);
        return $value == true;
    }

    /**
     * Validate the value.
     * @param mixed $value
     * @return array Associative array of $id => $message.
     */
    #[Override]
    protected function getValidationErrors($value): array
    {
        $errors = [];
        if ($this->getRequired()) {
            if (!$value) {
                $errors[$this->getID() . '_error_required'] = '<strong>' . $this->getLabel() . '</strong> is a required field.';
            }
        }
        return $errors;
    }

    #endregion
}
