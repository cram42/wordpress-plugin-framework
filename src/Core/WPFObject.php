<?php

namespace WPPluginFramework;

class WPFObject
{
    use Traits\RequiresWPF;

    /**
     * Storage for getInstance().
     * @var array{ {string}: object }
     */
    private static array $wpfobject_instance_storage = [];

    protected mixed $wpfobject_requirements = null;
    protected mixed $wpfobject_class_file = null;
    protected mixed $wpfobject_events = null;

    public function __construct()
    {
    }

    /**
     * Fires the method(s) for an event interface.
     * @param string $event The event interface name.
     * @return void
     */
    public function fireEvent(string $event): void
    {
        Logger::debug('fireEvent("'.$event.'")', get_class(), get_called_class());
        if (in_array($event, $this->getEvents())) {
            foreach (get_class_methods($event) as $method) {
                if (method_exists($this, $method)) {
                    $this->$method();
                } else {
                    Logger::warning(
                        sprintf('fireEvent("%s") Method "%s" does not exist', $event, $method),
                        get_class(),
                        get_called_class()
                    );
                }
            }
        }
    }

    /**
     * Get the class file path from the decendent class.
     * @return string The absolute path to the class file.
     */
    public function getClassFile(): string
    {
        if (!$this->wpfobject_class_file) {
            $class = get_called_class();
            $reflection = new \ReflectionClass($class);
            $this->wpfobject_class_file = $reflection->getFileName();
        }
        return $this->wpfobject_class_file;
    }

    /**
     * Get the events implemented by the decendent class.
     * @return array
     */
    public function getEvents(): array
    {
        if (!$this->wpfobject_events) {
            $this->wpfobject_events = [];
            $class = get_called_class();
            foreach (class_implements($class, true) as $interface) {
                if (is_subclass_of($interface, WPF_INTERFACE_EVENT)) {
                    $this->wpfobject_events[] = $interface;
                }
            }
        }
        return $this->wpfobject_events;
    }

    /**
     * Return and/or create the running instance.
     * @return object The running instance.
     */
    public static function getInstance(): object
    {
        // The child class
        $class = get_called_class();

        // Create a new instance if required
        if (!isset(static::$wpfobject_instance_storage[$class])) {
            static::$wpfobject_instance_storage[$class] = new static();
        }

        // Return instance
        return static::$wpfobject_instance_storage[$class];
    }

    /**
     * Return and/or generate the required plugins.
     * These are identified as properties starting with REQUIRES_PREFIX.
     * The value of the property is the required plugin name.
     * These should be added as traits.
     * @return array Numeric array of required plugin names.
     */
    public function getRequirements(): array
    {
        if (!$this->wpfobject_requirements) {
            $class = get_called_class();
            $reflection = new \ReflectionClass($class);
            $requirements = [];

            foreach ($reflection->getProperties() as $property) {
                $property_name = $property->name;
                if (strprecmp($property_name, WPF_REQUIRES_PREFIX) == 0) {
                    $value = $class::$$property_name;
                    array_push($requirements, $value);
                }
            }

            $this->wpfobject_requirements = array_unique($requirements);
        }

        return $this->wpfobject_requirements;
    }
}
