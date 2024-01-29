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

    protected string $sort_by = 'label';

    private array $list_items = [];

    /**
     * Get all items. Inactives optional.
     */
    public function getListItems(bool $include_inactive = false): array
    {
        if (count($this->list_items) == 0) {
            $this->list_items = array_filter(
                $this->getAll($this->sort_by),
                fn ($item) => $include_inactive || ($item['inactive'] == false)
            );
        }
        return $this->list_items;
    }

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
