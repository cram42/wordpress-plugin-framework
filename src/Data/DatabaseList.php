<?php

namespace WPPluginFramework\Data;

use WPPluginFramework\Data\Attributes\{
    Column,
    Required,
    Unique,
};

class DatabaseList extends DatabaseTable
{
    #[Column, Required]
    public string $value;

    #[Column, Required, Unique]
    public string $label;

    #[Column]
    public bool $inactive = false;

    /**
     * Trim class suffixes from name.
     * @return string
     */
    #[Override]
    protected function trimClassSuffixes(string $name): string
    {
        $name = parent::trimClassSuffixes($name);
        return preg_replace('/List$/', '', $name);
    }
}
