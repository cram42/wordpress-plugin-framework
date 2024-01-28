<?php

namespace WPPluginFramework;

abstract class Plugin extends WPFParentObject
{
    #region Protected Properties

    /**
     * Associative array of namespaces to arrays of relative paths.
     * @var array<string: string[]>
     */
    protected array $relative_namespaces = [];

    /**
     * Array of fully-qualified class names to load on startup.
     * @var array<string>
     */
    protected array $children = [];

    protected string|null $plugin_file = null;

    #endregion
    #region Constructor

    public function __construct()
    {
        $this->loadNamespaces();
        $this->loadChildren();

        if (!$this->checkRequirements()) {
            return;
        }
        $this->hookEvents();
    }

    #endregion
    #region Public Methods

    public static function onPluginActivation(): void
    {
        Logger::debug('onPluginActivation()', get_class(), get_called_class());
        static::getInstance()->fireEvent(WPF_INTERFACE_ENABLE_EVENT);
    }

    public static function onPluginDeactivation(): void
    {
        Logger::debug('onPluginDeactivation()', get_class(), get_called_class());
        static::getInstance()->fireEvent(WPF_INTERFACE_DISABLE_EVENT);
    }

    public static function onPluginUninstall(): void
    {
        Logger::debug('onPluginUninstall()', get_class(), get_called_class());
        static::getInstance()->fireEvent(WPF_INTERFACE_UNINSTALL_EVENT);
    }

    public static function onPluginsLoaded(): void
    {
        Logger::debug('onPluginsLoaded()', get_class(), get_called_class());
        static::getInstance()->fireEvent(WPF_INTERFACE_LOAD_EVENT);
    }

    public static function onRESTAPIInit(): void
    {
        Logger::debug('onRESTAPIInit()', get_class(), get_called_class());
        static::getInstance()->fireEvent(WPF_INTERFACE_REST_EVENT);
    }

    #endregion
    #region Protected Methods

    protected function getPluginFile(): string
    {
        if (!$this->plugin_file) {
            $reflection = new \ReflectionClass($this);
            $class_file = $reflection->getFileName();

            foreach (get_plugins() as $file => $data) {
                $plugin_file = WP_PLUGIN_DIR . '/' . $file;
                $plugin_dir = dirname($plugin_file);
                if (strprecmp($class_file, $plugin_dir) == 0) {
                    $this->plugin_file = $plugin_file;
                    break;
                }
            }
        }
        return $this->plugin_file;
    }

    protected function hookCustomEvents(): void
    {
    }

    #endregion
    #region Private Methods

    /**
     * Check requirements against active plugins.
     * Display warning in wp-admin if requirements not met.
     * @return bool Requirements met?
     */
    private function checkRequirements(): bool
    {
        $missing_requirements = [];
        $requirements = $this->getRequirements();

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_data = get_plugin_data($this->getClassFile());
        $this_plugin_name = $plugin_data['Name'];

        foreach (get_plugins() as $file => $data) {
            $plugin_name = $data['Name'];
            if (in_array($plugin_name, $requirements)) {
                if (!is_plugin_active($file)) {
                    $missing_requirements[] = $plugin_name;
                }
            }
        }

        if (count($missing_requirements) > 0) {

            Logger::warning(
                sprintf(
                    'Not loaded due to missing requirements: %s',
                    implode(', ', $missing_requirements)
                ),
                get_class(),
                get_called_class()
            );

            // Show wp-admin warning
            add_action('admin_notices', function () use ($this_plugin_name, $missing_requirements) {
                $html = <<<END
                    <div class="notice notice-warning">
                        <p>
                            <b>%s</b> %s:
                        </p>
                        <ul class="ul-disc">
                            %s
                        </ul>
                    </div>
                END;

                $list_items = implode(
                    PHP_EOL,
                    array_map(
                        fn ($req) => sprintf('<li>%s</li>', $req),
                        $missing_requirements
                    )
                );

                printf(
                    $html,
                    $this_plugin_name,
                    __('is not loaded because the following plugins are disabled', 'wpf'),
                    $list_items
                );
            });

            return false;
        } else {
            return true;
        }
    }

    private function hookEvents(): void
    {
        $plugin_class = get_called_class();

        register_activation_hook($this->getPluginFile(), [$plugin_class, 'onPluginActivation']);
        register_deactivation_hook($this->getPluginFile(), [$plugin_class, 'onPluginDeactivation']);
        register_uninstall_hook($this->getPluginFile(), [$plugin_class, 'onPluginUninstall']);

        add_action('plugins_loaded', [$plugin_class, 'onPluginsLoaded']);
        add_action('rest_api_init', [$plugin_class, 'onRESTAPIInit']);

        $this->hookCustomEvents();
    }

    /**
     * Load child classes by name into WPF.
     * @return void
     */
    private function loadChildren(): void
    {
        foreach ($this->children as $child) {
            // Ensure child class exists
            if (!class_exists($child, true)) {
                $error = sprintf('Child class does not exist: "%s"', $child);
                Logger::error($error, get_class(), get_called_class());
                wp_die(Logger::error($error, get_class(), get_called_class()));
            }

            // Get running instance and add to WPF
            $instance = $child::getInstance();
            $this->addWPFChild($instance);
        }
    }

    /**
     * Load namespaces with relative paths into ClassFinder.
     * @return void
     */
    private function loadNamespaces(): void
    {
        $class_file = $this->getClassFile();
        $class_dir = dirname($class_file);

        foreach ($this->relative_namespaces as $namespace => $paths) {
            foreach ($paths as $relative_path) {
                $absolute_path = $class_dir . '/' . trim($relative_path, DIRECTORY_SEPARATOR);
                ClassFinder::addNamespacePath($namespace, $absolute_path);
            }
        }
    }

    #endregion
}
