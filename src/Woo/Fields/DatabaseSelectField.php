<?php

namespace WPPluginFramework\Woo\Fields;

use WPPluginFramework\Logger;

class DatabaseSelectField extends SelectField
{
    #region Protected Properties

    protected ?string $database_list = null;

    #endregion
    #region Private Properties

    private ?object $resource_instance = null;

    #endregion
    #region Protected Methods

    /**
     * Get or generate the resource instance.
     * @return object
     */
    protected function getResource(): object
    {
        if (!$this->resource_instance) {
            if (!$this->database_list) {
                $error = 'database_list not specified';
                Logger::error($error, get_class(), get_called_class());
                throw new \Exception($error);
            }
            if (!class_exists($this->database_list, true)) {
                $error = sprintf('Class "" does not exist', $this->database_list);
                Logger::error($error, get_class(), get_called_class());
                throw new \Exception($error);
            }
            $this->resource_instance = $this->database_list::getInstance();
        }
        return $this->resource_instance;
    }

    /**
     * Get the options, injecting a blank if allowed.
     * @return array
     */
    protected function getOptions(): array
    {
        if (count($this->options) == 0) {
            foreach ($this->getResource()->getListItems() as $item) {
                $this->options[$item['value']] = $item['label'];
            }
        }

        if ($this->allow_blank) {
            if (!array_key_exists('', $this->options)) {
                $this->options = array_merge(
                    ['' => ''],
                    $this->options
                );
            }
        }

        return $this->options;
    }

    #endregion
}
