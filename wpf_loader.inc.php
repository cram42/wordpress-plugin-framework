<?php

namespace WPPluginFramework;

/**
 * Loads the plugin framework, or handles plugin if missing.
 * 
 * Usage:
 * 
 * ```
 * require_once 'lib/wordpress-plugin-framework/wpf_loader.inc.php';
 * if (!\WPPluginFramework\Loader::load(__FILE__)) {
 *     return;
 * }
 * ```
 */
class Loader
{
    private static array $plugins = [];

    public static function load(string $plugin_file): bool
    {
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_data = \get_plugin_data($plugin_file);
        static::$plugins[] = $plugin_data['Name'];

        $wpf_path = static::getWPFPath();

        if ($wpf_path === null) {
            register_activation_hook($plugin_file, [static::class, 'doActivationFailure']);
            register_deactivation_hook($plugin_file, [static::class, 'doDeactivationFailure']);
            register_uninstall_hook($plugin_file, [static::class, 'doUninstallFailure']);
            add_action('admin_notices', [static::class, 'doAdminWarning']);
            return false;
        }

        require_once $wpf_path;
        return true;
    }

    private static function die(string $action): void
    {
        $message = sprintf(
            <<<EOL
            Unable to %s plugin. <b>WordPress Plugin Framework</b> is not installed.<br>
            Without the framework, none of the actions will work, including cleanup.<br>
            Please install the framework, then run this action.
            EOL,
            $action
        );
        wp_die($message);
    }

    public static function doActivationFailure(): void
    {
        static::die('activate');
    }

    public static function doDeactivationFailure(): void
    {
        static::die('deactivate');
    }

    public static function doUninstallFailure(): void
    {
        static::die('uninstall');
    }

    public static function doAdminWarning(): void
    {
        echo('<div class="notice notice-error">');
        echo('<p>');
        echo('<b>WordPress Plugin Framework</b> is not installed.<br>');
        echo('Without it, the following plugins will not work:');
        echo('</p>');
        echo('<ul class="ul-disc">');
        foreach (static::$plugins as $plugin) {
            echo('<li>' . $plugin . '</li>');
        }
        echo('</ul>');
        echo('</div>');
    }

    private static function getWPFPath(): string|null
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = array_filter(
            get_plugins(),
            fn ($d) => $d['Name'] == 'WordPress Plugin Framework'
        );

        if (count($plugins) == 0) {
            return null;
        }

        return WP_PLUGIN_DIR . '/' . array_key_first($plugins);
    }
}
