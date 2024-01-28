<?php

namespace WPPluginFramework;

require_once dirname(__FILE__) . '/../Logging/Logger.php';

//Logger::setLevel(__NAMESPACE__ . '\ClassFinder', LogLevel::DEBUG);

abstract class ClassFinder
{
    #region Private Properties

    /** @var bool */
    private static bool $is_registered = false;

    /**
     * Associative array of namespaces and full search paths.
     * @var array<string, string[]>
     */
    private static array $namespaces = array();

    #endregion
    #region Public Methods

    /**
     * @param string $namespace The fully-qualified namespace.
     * @param string|string[] $path The absolute path or paths to search.
     * @param bool $absolute Is the provided path absolute or relative.
     * @return void
     */
    public static function addNamespacePath(string $namespace, mixed $path): void
    {
        // Handle arrays
        if (is_array($path)) {
            foreach ($path as $path_item) {
                static::addNamespacePath($namespace, $path_item, $absolute);
            }
            return;
        }

        // Normalize inputs
        $namespace = static::normalizeNamespace($namespace);
        $path = static::normalizePath($path);

        // Initialize item if needed
        if (!isset(static::$namespaces[$namespace])) {
            static::$namespaces[$namespace] = array();
        }

        // Add path if not exists
        if (!in_array($path, static::$namespaces[$namespace])) {
            Logger::debug(sprintf('addNamespacePath("%s", "%s")', $namespace, $path), static::class);
            array_push(static::$namespaces[$namespace], $path);
        }
    }

    /**
     * Find the class file for a given class.
     * @param string $class The fully-qualified name of the class.
     * @return string|bool The absolute path of the class file or null if not found.
     */
    public static function loadClass(string $class): mixed
    {
        $namespace = $class;
        $path_parts = array();

        while ($tail = static::popNamespace($namespace)) {
            // Add tail to relative path
            array_unshift($path_parts, $tail);

            // Is this namespace registered?
            if (isset(static::$namespaces[$namespace])) {
                Logger::debug(sprintf('loadClass("%s") Namespace: "%s"', $class, $namespace), static::class);
                $sub_path = implode(DIRECTORY_SEPARATOR, $path_parts) . '.php';

                // Check each registered search path
                foreach (static::$namespaces[$namespace] as $root_path) {
                    $full_path = $root_path . $sub_path;

                    // Does this file exist?
                    if (file_exists($full_path)) {
                        Logger::debug(sprintf('loadClass("%s") File: "%s"', $class, $full_path), static::class);

                        $file_data = static::readClassFile($full_path);

                        // Check if the class exists in this file
                        foreach ($file_data['classes'] as $file_class) {
                            if ($file_class['full_name'] == $class) {
                                Logger::debug(sprintf('loadClass("%s") class', $class), static::class);
                                require $full_path;
                                return $full_path;
                            }
                        }

                        // Check if the interface exists in this file
                        foreach ($file_data['enums'] as $file_enum) {
                            if ($file_enum['full_name'] == $class) {
                                Logger::debug(sprintf('loadClass("%s") enum', $class), static::class);
                                require $full_path;
                                return $full_path;
                            }
                        }

                        // Check if the interface exists in this file
                        foreach ($file_data['interfaces'] as $file_interface) {
                            if ($file_interface['full_name'] == $class) {
                                Logger::debug(sprintf('loadClass("%s") interface', $class), static::class);
                                require $full_path;
                                return $full_path;
                            }
                        }

                        // Check if the trait exists in this file
                        foreach ($file_data['traits'] as $file_trait) {
                            if ($file_trait['full_name'] == $class) {
                                Logger::debug(sprintf('loadClass("%s") trait', $class), static::class);
                                require $full_path;
                                return $full_path;
                            }
                        }

                        Logger::warning(
                            sprintf('Class "%s" not found in file "%s"', $class, $full_path),
                            static::class
                        );
                    }
                }
            }
        }

        // Not found
        return false;
    }

    /**
     * Register the loadClass function with SPL autoload.
     * @return void
     */
    public static function register(): void
    {
        if (!static::$is_registered) {
            Logger::debug('register()', static::class);
            spl_autoload_register(array(static::class, 'loadClass'));
            static::$is_registered = true;
        }
    }

    #endregion
    #region Private Methods

    /**
     * Normalize namespaces to "\Some\Namespace".
     * @param string $namespace
     * @return string
     */
    private static function normalizeNamespace(string $namespace): string
    {
        return rtrim($namespace, '\\');
    }

    /**
     * Normalize paths to "/some/path/thingo/".
     * @param string $path
     * @return string
     */
    private static function normalizePath(string $path): string
    {
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Pops and returns the last part of a namespace, while modifying original.
     * "Some\Namespace\Thingo" is edited by reference to "Some\Namespace" and "Thingo" is returned.
     * @param string byref $namespace The namespace string.
     * @return string|null The popped last part, or null if failed.
     */
    private static function popNamespace(&$namespace): mixed
    {
        $pos = strrpos($namespace, '\\');
        if ($pos === false) {
            return null;
        }
        $tail = substr($namespace, $pos + 1);
        $namespace = substr($namespace, 0, $pos);
        return $tail;
    }

    /**
     * Reads a PHP file and extracts the names of all classes and their namespaces.
     * @param string $path Path to the PHP file.
     * @return array{classes:array{class:string, namespace:string, full_name:string}, interfaces:{}, traits:{}}
     */
    private static function readClassFile($path)
    {
        $lines = file($path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $classes = array();
        $enums = array();
        $interfaces = array();
        $traits = array();

        $namespace = '\\';
        $depth = 0;

        $regex_namespace = '/^namespace (\S+);/';
        $regex_class = '/^(?:abstract\s)*class (\S+)/';
        $regex_enum = '/^enum ([^\s:]+)/';
        $regex_interface = '/^interface (\S+)/';
        $regex_trait = '/^trait (\S+)/';

        foreach ($lines as $line) {

            // Only look for namespaces and classes at root depth
            if ($depth == 0) {
                // compare regexes
                preg_match($regex_namespace, $line, $matches_namespace);
                preg_match($regex_class, $line, $matches_class);
                preg_match($regex_enum, $line, $matches_enum);
                preg_match($regex_interface, $line, $matches_interface);
                preg_match($regex_trait, $line, $matches_trait);

                // Update the current namespace
                if (count($matches_namespace) > 0) {
                    $namespace = $matches_namespace[1];
                    Logger::debug(sprintf('Found namespace: "%s"', $namespace), get_class(), get_called_class());
                }

                // Store the class name and current namespace
                if (count($matches_class) > 0) {
                    $class = $matches_class[1];
                    Logger::debug(sprintf('Found class: "%s"', $class), get_class(), get_called_class());
                    array_push($classes, array(
                        'class' => $class,
                        'namespace' => $namespace,
                        'full_name' => $namespace . '\\' . $class,
                    ));
                }

                // Store the enum name and current namespace
                if (count($matches_enum) > 0) {
                    $enum = $matches_enum[1];
                    Logger::debug(sprintf('Found enum: "%s"', $enum), get_class(), get_called_class());
                    array_push($enums, array(
                        'enum' => $enum,
                        'namespace' => $namespace,
                        'full_name' => $namespace . '\\' . $enum,
                    ));
                }

                // Store the interface name and current namespace
                if (count($matches_interface) > 0) {
                    $interface = $matches_interface[1];
                    Logger::debug(sprintf('Found interface: "%s"', $interface), get_class(), get_called_class());
                    array_push($interfaces, array(
                        'interface' => $interface,
                        'namespace' => $namespace,
                        'full_name' => $namespace . '\\' . $interface,
                    ));
                }

                // Store the trait name and current namespace
                if (count($matches_trait) > 0) {
                    $trait = $matches_trait[1];
                    array_push($traits, array(
                        'trait' => $trait,
                        'namespace' => $namespace,
                        'full_name' => $namespace . '\\' . $trait,
                    ));
                }
            }

            // Adjust depth
            $depth += substr_count($line, '{');
            $depth -= substr_count($line, '}');
        }

        return array(
            'classes' => $classes,
            'enums' => $enums,
            'interfaces' => $interfaces,
            'traits' => $traits,
        );
    }

    #endregion
}

ClassFinder::register();
