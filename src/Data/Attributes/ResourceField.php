<?php

namespace WPPluginFramework\Data\Attributes;

use Attribute;

#[Attribute]
class ResourceField implements IResourceFieldAttribute
{
    public string $name;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}