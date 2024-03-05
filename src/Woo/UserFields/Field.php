<?php

namespace WPPluginFramework\Woo\UserFields;

use WPPluginFramework\{
    Logger, LogLevel,
    WPFObject,
};
use WPPluginFramework\Events\{
    ILoadEvent,
    IDisableEvent,
    IUninstallEvent,
};
use WPPluginFramework\Traits\RequiresWooCommerce;

use function WPPluginFramework\{
    strprecmp,
    strsuffix
};

abstract class Field extends WPFObject implements
    ILoadEvent,
    IDisableEvent,
    IUninstallEvent
{
    use RequiresWooCommerce;

    protected const TEMPLATE_MY_ACCOUNT = 'myaccount/field.php';
    protected const TEMPLATE_PROFILE = 'wp-admin/user-edit/field.php';
    protected const TEMPLATE_REGISTRATION = 'myaccount/form-login/field.php';

    #region Private Properties

    private FieldContext|int $_context = FieldContext::UNKNOWN;
    private int $_current_user_id = 0;
    private mixed $_current_value = null;

    #endregion
    #region Protected Properties

    protected ?string $id = null;
    protected ?string $label = null;
    protected bool $show_description = false;
    protected ?string $description = null;

    protected ?bool $disabled = null;
    protected ?bool $required = null;

    protected bool $show_in_profile = true;
    protected ?string $profile_table = null;

    protected bool $show_in_myaccount = true;
    protected ?string $myaccount_tab = null;

    protected bool $show_in_registration = true;

    protected bool $wipe_on_disable = false;
    protected bool $wipe_on_uninstall = true;

    #endregion
    #region Public Methods

    /**
     * Get or generate the field description.
     * @return string
     */
    public function getDescription($context = null): string
    {
        if (!$this->description) {
            $this->description = sprintf(
                'Added by %s',
                get_called_class()
            );
        }
        return $this->show_description ? $this->description : '';
    }

    /**
     * Get or generate the field ID.
     * @return string
     */
    public function getID($context = null): string
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
    public function getLabel($context = null): string
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
     * @return bool
     */
    public function getDisabled(): bool
    {
        if ($this->disabled === null) {
            $this->disabled = false;
        }
        return $this->disabled;
    }

    /**
     * @return bool
     */
    public function getRequired(): bool
    {
        if ($this->required === null) {
            $this->required = false;
        }
        return $this->required;
    }

    /**
     * Get the current context.
     * @return array [$context, $current_value, $current_user_id]
     */
    public function getContext(): array
    {
        return [
            $this->_context,
            $this->_current_value,
            $this->_current_user_id,
        ];
    }

    #region Action Hooks

    /**
     * Runs when Edit Profile is submitted.
     * Add validation errors here.
     * @param WP_Error $errors
     * @param bool $update
     * @param stdClass $user
     * @return void
     */
    public function onUserProfileErrors($errors, $update, $user): void
    {
        Logger::debug('onUserProfileErrors()', get_class(), get_called_class());
        $this->setContext(FieldContext::PROFILE);
        $this->setContextUserId($user->ID);
    }

    /**
     * Runs when Edit Profile is submitted.
     * Save metadata here.
     * @param int $user_id
     * @return void
     */
    public function onUserProfileUpdate(int $user_id): void
    {
        Logger::debug('onUserProfileUpdate()', get_class(), get_called_class());
        $this->setContext(FieldContext::PROFILE);
        $this->setContextUserId($user_id);
        $this->setContextValue($this->getSavedValue($user_id));
        $value = $this->getPostedValue();
        if ($value !== null) {
            $value = $this->sanitizeValue($value);
            update_user_meta($user_id, $this->getID(), $value);
        }
    }

    /**
     * Runs when a new customer is created.
     * Save metadata here.
     * @param int $customer_id
     * @return void
     */
    public function onWooCreatedCustomer(int $customer_id): void
    {
        Logger::debug('onWooCreatedCustomer()', get_class(), get_called_class());
        $this->setContext(FieldContext::REGISTRATION);
        $value = $this->getPostedValue();
        $value = $this->sanitizeValue($value);
        update_user_meta($customer_id, $this->getID(), $value);
    }

    /**
     * Runs when the Edit Account Form is shown.
     * Draw here.
     * @return void
     */
    public function onWooEditAccountForm(): void
    {
        Logger::debug('onWooEditAccountForm()', get_class(), get_called_class());
        $this->setContext(FieldContext::MY_ACCOUNT);
        $user = wp_get_current_user();
        $this->setContextUserId($user->ID);
        $this->setContextValue($this->getSavedValue($user->ID));
        wc_get_template($this->getTemplateName(), ['field' => $this]);
    }

    /**
     * Runs when the Registration Form is shown.
     * Draw here.
     * @return void
     */
    public function onWooRegisterForm(): void
    {
        Logger::debug('onWooRegisterForm()', get_class(), get_called_class());
        $this->setContext(FieldContext::REGISTRATION);
        wc_get_template($this->getTemplateName(), ['field' => $this]);
    }

    /**
     * Runs when the Registration Form is submitted.
     * Add validation errors here.
     * @param string $username
     * @param string $email
     * @param WP_Error $validation_errors
     * @return void
     */
    public function onWooRegisterPost(string $username, string $email, \WP_Error $validation_errors): void
    {
        Logger::debug('onWooRegisterPost()', get_class(), get_called_class());
        $this->setContext(FieldContext::REGISTRATION);
        $value = $this->getPostedValue();
        $errors = $this->getValidationErrors($value);
        foreach ($errors as $id => $message) {
            $validation_errors->add($id, $message);
        }
    }

    /**
     * Runs when a customer's details are saved.
     * Save metadata here.
     * @param int $customer_id
     * @return void
     */
    public function onWooSaveAccountDetails(int $customer_id): void
    {
        Logger::debug('onWooSaveAccountDetails()', get_class(), get_called_class());
        $this->setContext(FieldContext::MY_ACCOUNT);
        $value = $this->getPostedValue();
        if ($value !== null) {
            $value = $this->sanitizeValue($value);
            update_user_meta($customer_id, $this->getID(), $value);
        }
    }

    /**
     * Runs when a customer's details are validated.
     * Validate here.
     * @param array $validation_errors
     * @param int $customer_id
     * @return void
     */
    public function onWooSaveAccountDetailsErrors(array $validation_errors, int $customer_id): void
    {
        Logger::debug('onWooSaveAccountDetailsErrors()', get_class(), get_called_class());
        $this->setContext(FieldContext::MY_ACCOUNT);
        $value = $this->getPostedValue();
        $errors = $this->getValidationErrors($value);
        foreach ($errors as $id => $message) {
            $validation_errors->add($id, $message);
        }
    }

    /**
     * Runs when the WPF My Account Tab is shown.
     * Draw here.
     * @param string $tab_id
     * @param int $user_id
     * @return void
     */
    public function onWPFMyAccountTab(int $user_id): void
    {
        Logger::debug('onWPFMyAccountTab()', get_class(), get_called_class());
        $this->setContext(FieldContext::MY_ACCOUNT);
        $this->setContextUserId($user_id);
        $this->setContextValue($this->getSavedValue($user_id));
        wc_get_template($this->getTemplateName(), ['field' => $this]);
    }

    /**
     * Runs when the WPF My Account Tab is submitted.
     * Validate here. Return an array of errors.
     * @param array $errors
     * @param int $user_id
     * @return array
     */
    public function onWPFMyAccountTabErrors(array $errors, int $user_id): array
    {
        Logger::debug('onWPFMyAccountTabErrors()', get_class(), get_called_class());
        $this->setContext(FieldContext::MY_ACCOUNT);
        $this->setContextUserId($user_id);
        $this->setContextValue($this->getSavedValue($user_id));
        $value = $this->getPostedValue();
        $new_errors = $this->getValidationErrors($value);
        return array_merge($errors, $new_errors);
    }

    /**
     * Runs when the WPF My Account Tab is saved.
     * Save metadata here.
     * @param int $user_id
     * @return void
     */
    public function onWPFMyAccountTabSave(int $user_id): void
    {
        Logger::debug('onWPFMyAccountTabSave()', get_class(), get_called_class());
        $this->setContext(FieldContext::MY_ACCOUNT);
        $this->setContextUserId($user_id);
        $this->setContextValue($this->getSavedValue($user_id));
        $value = $this->getPostedValue();
        if ($value !== null) {
            $value = $this->sanitizeValue($value);
            update_user_meta($user_id, $this->getID(), $value);
        }
    }

    /**
     * Runs when the WPF Profile Table is shown.
     * Draw here.
     * @param int $user_id
     * @return void
     */
    public function onWPFProfileTable(int $user_id): void
    {
        Logger::debug('onWPFProfileTable()', get_class(), get_called_class());
        $this->setContext(FieldContext::PROFILE);
        $this->setContextUserId($user_id);
        $this->setContextValue($this->getSavedValue($user_id));
        wc_get_template($this->getTemplateName(), ['field' => $this]);
    }

    #endregion
    #region Protected Methods

    /**
     * Set the current context.
     * @param FieldContext|int $value
     * @return void
     */
    protected function setContext(FieldContext|int $value): void
    {
        $this->_context = $value;
    }

    /**
     * Set the current value.
     * @param int $value
     * @return void
     */
    protected function setContextUserId(int $value): void
    {
        $this->_current_user_id = $value;
    }

    /**
     * Set the current value.
     * @param mixed $value
     * @return void
     */
    protected function setContextValue(mixed $value): void
    {
        $this->_current_value = $value;
    }

    /**
     * Get the value from $_POST.
     * Return the value or null to skip saving.
     * @return mixed
     */
    protected function getPostedValue(): mixed
    {
        return array_key_exists($this->getID(), $_POST) ? $_POST[$this->getID()] : null;
    }

    /**
     * Get the saved value.
     * @param int $user_id
     * @return mixed
     */
    protected function getSavedValue(int $user_id): mixed
    {
        return get_user_meta($user_id, $this->getID(), true);
    }

    /**
     * Get the template name for the current context.
     * @return string
     */
    protected function getTemplateName(): string
    {
        [$context] = $this->getContext();
        switch ($context) {
            case FieldContext::MY_ACCOUNT: return static::TEMPLATE_MY_ACCOUNT;
            case FieldContext::PROFILE: return static::TEMPLATE_PROFILE;
            case FieldContext::REGISTRATION: return static::TEMPLATE_REGISTRATION;
            default: return '';
        }
    }

    /**
     * Validate the value.
     * @param mixed $value
     * @return array Associative array of $id => $message.
     */
    protected function getValidationErrors($value): array
    {
        Logger::debug('getValidationErrors()', get_class(), get_called_class());
        $errors = [];

        if ($this->getRequired() && !$this->getDisabled()) {
            if ($value == '') {
                $errors[$this->getID() . '_error_required'] = '<strong>' . $this->getLabel() . '</strong> is a required field.';
            }
        }

        return $errors;
    }

    /**
     * Sanitize value for storage.
     * @param mixed $value
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

        // Add template
        add_filter('wc_get_template', function ($located, $template) {
            if (
                $template == static::TEMPLATE_MY_ACCOUNT ||
                $template == static::TEMPLATE_PROFILE ||
                $template == static::TEMPLATE_REGISTRATION
            ) {
                $wc_root = dirname(WPF_PATH) . '/woocommerce';
                if (strprecmp($located, $wc_root) == 0) {
                    $located = WPF_PATH . 'page-templates/' . $template;
                }
            }
            return $located;
        }, 90, 2);

        // Edit User Profile
        if ($this->show_in_profile) {
            if ($this->profile_table != null) {
                add_action('wpf_profiletable_' . $this->profile_table, [$this, 'onWPFProfileTable']);
                add_action('user_profile_update_errors', [$this, 'onUserProfileErrors'], 10, 3);
                add_action('personal_options_update', [$this, 'onUserProfileUpdate']);
                add_action('edit_user_profile_update', [$this, 'onUserProfileUpdate']);
            } else {
                Logger::error('profile_table is not set', get_class(), get_called_class());
            }
        }

        // My Account
        if ($this->show_in_myaccount) {
            if ($this->myaccount_tab != null) {
                // Seperate Tab
                add_action('wpf_myaccounttab_' . $this->myaccount_tab, [$this, 'onWPFMyAccountTab']);
                add_filter('wpf_myaccounttab_' . $this->myaccount_tab . '_errors', [$this, 'onWPFMyAccountTabErrors'], 10, 2);
                add_filter('wpf_myaccounttab_' . $this->myaccount_tab . '_save', [$this, 'onWPFMyAccountTabSave']);
            } else {
                // Account Details Tab
                add_action('woocommerce_edit_account_form', [$this, 'onWooEditAccountForm']);
                add_action('woocommerce_save_account_details_errors', [$this, 'onWooSaveAccountDetailsErrors'], 10, 2);
                add_action('woocommerce_save_account_details', [$this, 'onWooSaveAccountDetails']);
            }
        }

        // Registration Form
        if ($this->show_in_registration) {
            add_action('woocommerce_register_form', [$this, 'onWooRegisterForm']);
            add_action('woocommerce_register_post', [$this, 'onWooRegisterPost'], 10, 3);
            add_action('woocommerce_created_customer', [$this, 'onWooCreatedCustomer']);
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
