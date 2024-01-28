<?php

namespace WPPluginFramework;

class WPFParentObject extends WPFObject
{
    private array $wpfparentobject_children = [];
    protected array $valid_child_types = [ __NAMESPACE__ . '\WPFObject' ];

    public function __construct()
    {
        parent::__construct();
    }

    public function addWPFChild(WPFObject $child): void
    {
        $name = get_class($child);
        $is_valid_type = false;
        foreach ($this->valid_child_types as $valid_type) {
            if (is_a($name, $valid_type, true)) {
                $is_valid_type = true;
                break;
            }
        }

        if ($is_valid_type) {
            $this->wpfparentobject_children[$name] = $child;
        } else {
            $error = sprintf('Child "%s" is not a valid child type', $name);
            Logger::error($error, get_class(), get_called_class());
            wp_die($error);
        }
    }

    public function getWPFChildren(): array
    {
        return $this->wpfparentobject_children;
    }

    /**
     * Fires the method(s) for an event interface on self and children.
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
                }
            }

            foreach ($this->getWPFChildren() as $child) {
                $child->fireEvent($event);
            }
        }
    }

    /**
     * Get the events implemented by the decendent class and children.
     * @return array
     */
    public function getEvents(): array
    {
        if (!$this->wpfobject_events) {
            $events = parent::getEvents();

            foreach ($this->getWPFChildren() as $child) {
                $events = array_merge($events, $child->getEvents());
            }

            $this->wpfobject_events = array_values(array_unique($events));
        }
        return $this->wpfobject_events;
    }

    /**
     * Return and/or generate the required plugins.
     * Merged array of requirements from this class and children.
     * @return array Numeric array of required plugin names.
     */
    public function getRequirements(): array
    {
        if (!$this->wpfobject_requirements) {
            $requirements = parent::getRequirements();

            foreach ($this->getWPFChildren() as $child) {
                $requirements = array_merge($requirements, $child->getRequirements());
            }

            $this->wpfobject_requirements = array_values(array_unique($requirements));
        }
        
        return $this->wpfobject_requirements;
    }
}
