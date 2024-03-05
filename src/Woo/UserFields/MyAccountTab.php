<?php

namespace WPPluginFramework\Woo\UserFields;

use WPPluginFramework\{
    Logger, LogLevel,
};
use WPPluginFramework\Events\{
    IInitEvent,
    ILoadEvent,
    IEnableEvent,
    IDisableEvent,
};
use WPPluginFramework\Woo\WPFWooObject;

use function WPPluginFramework\{
    strprecmp,
    strsuffix
};

Logger::setLevel(__NAMESPACE__ . '\MyAccountTab', LogLevel::DEBUG);

abstract class MyAccountTab extends WPFWooObject implements
    IInitEvent,
    ILoadEvent,
    IEnableEvent,
    IDisableEvent
{
    #region Protected Properties

    protected ?string $id = null;
    protected ?string $slug = null;
    protected ?string $title = null;

    #endregion
    #region Public Methods

    /**
     * Get or generate the table ID.
     * @return string
     */
    public function getID(): string
    {
        if (!$this->id) {
            $temp = get_called_class();                                 // My\Namespace\CoolStuffMyAccountTab
            $temp = preg_replace('/MyAccountTab$/', '', $temp);    // My\Namespace\CoolStuff
            $temp = str_replace('\\', '_', $temp);                      // My_Namespace_CoolStuff
            $temp = strtolower($temp);                                  // my_namespace_coolstuff
            $this->id = $temp;
        }
        return $this->id;
    }

    /**
     * Get or generate the table slug.
     * @return string
     */
    public function getSlug(): string
    {
        if (!$this->slug) {
            $temp = get_called_class();                                 // My\Namespace\CoolStuffMyAccountTab
            $temp = preg_replace('/MyAccountTab$/', '', $temp);    // My\Namespace\CoolStuff
            $temp = str_replace('\\', '-', $temp);                      // My-Namespace-CoolStuff
            $temp = strtolower($temp);                                  // my-namespace-coolstuff
            $this->slug = $temp;
        }
        return $this->slug;
    }

    /**
     * Get or generate the table label.
     * @return string
     */
    public function getTitle(): string
    {
        if (!$this->title) {
            $temp = get_called_class();                                     // My\Namespace\CoolStuffMyAccountTab
            $temp = strsuffix($temp, '\\');                                 // CoolStuffMyAccountTab
            $temp = preg_replace('/MyAccountTab$/', '', $temp);        // CoolStuff
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Cool Stuff
            $this->title = $temp;
        }
        return $this->title;
    }

    #region Action Hooks

    public function onTemplateRedirect(): void
    {
        Logger::debug('onTemplateRedirect()', get_class(), get_called_class());

        $action_name = 'save_' . $this->getID();
        $nonce_name = $action_name . '_nonce';

        // Validate action
        if (!array_key_exists('action', $_POST)) {
            return;
        }

        if ($_POST['action'] !== $action_name) {
            return;
        }

        // Validate nonce
        if (!array_key_exists($nonce_name, $_POST)) {
            return;
        }

        $nonce_value = wc_get_var($_POST[$nonce_name], wc_get_var($_POST['_wpnonce'], ''));
        if (!wp_verify_nonce($nonce_value, $action_name)) {
            return;
        }

        wc_nocache_headers();

        $errors = [];
        $errors = apply_filters('wpf_myaccounttab_' . $this->getID() . '_errors', $errors);
        foreach ($errors as $id => $message) {
            wc_add_notice($message, 'error', ['id' => $id]);
        }

        if (wc_notice_count('error') === 0) {
            do_action('wpf_myaccounttab_' . $this->getID() . '_save', get_current_user_id());
            wc_add_notice('Saved', 'notice');
        }
    }

    /**
     * Runs when the endpoint is shown.
     * @return void
     */
    public function onWooEndpoint(): void
    {
        Logger::debug('onWooEndpoint()', get_class(), get_called_class());

        $action_name = 'save_' . $this->getID();
        $nonce_name = $action_name . '_nonce';

        wc_get_template('myaccount/tab.php', [
            'action_name' => $action_name,
            'nonce_name' => $nonce_name,
            'user_id' => get_current_user_id(),
            'tab' => $this,
        ]);
    }

    #endregion

    /**
     * Register the endpoint.
     * Called on init and plugin enable.
     * @return void
     */
    public function registerEndpoint(): void
    {
        Logger::debug('registerEndpoint()', get_class(), get_called_class());
        add_rewrite_endpoint($this->getSlug(), EP_PAGES);
    }

    #endregion
    #region IInitEvent Implementation
    public function onInitEvent(): void
    {
        Logger::debug('onInitEvent()', get_class(), get_called_class());
        $this->registerEndpoint();
    }
    #endregion
    #region ILoadEvent Implementation
    public function onLoadEvent(): void
    {
        Logger::debug('onLoadEvent()', get_class(), get_called_class());

        // Add template
        // TODO: This doesn't need to be called on each instance
        add_filter('wc_get_template', function ($located, $template_name) {
            $wc_root = dirname(WPF_PATH) . '/woocommerce';
            if (strprecmp($located, $wc_root) == 0) {
                if ($template_name == 'myaccount/tab.php') {
                    $located = WPF_PATH . 'page-templates/myaccount/tab.php';
                }
            }
            return $located;
        }, 90, 2);

        // Add nav tab
        add_filter('woocommerce_account_menu_items', function ($nav_items) {
            $start = array_slice($nav_items, 0, -1, true);
            $end = array_slice($nav_items, -1, null, true);
            $nav_items = $start + [$this->getSlug() => $this->getTitle()] + $end;
            return $nav_items;
        });

        // Add slug to query vars
        add_filter('woocommerce_get_query_vars', function ($query_vars) {
            $query_vars[$this->getSlug()] = $this->getSlug();
            return $query_vars;
        });

        add_action('woocommerce_endpoint_' . $this->getSlug() . '_title', [$this, 'getTitle']);
        add_action('woocommerce_account_' . $this->getSlug() . '_endpoint', [$this, 'onWooEndpoint']);
        add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }
    #endregion
    #region IEnableEvent Implementation
    public function onEnableEvent(): void
    {
        $this->registerEndpoint();
        flush_rewrite_rules();
    }
    #endregion
    #region IDisableEvent Implementation
    public function onDisableEvent(): void
    {
        flush_rewrite_rules();
    }
    #endregion
}
