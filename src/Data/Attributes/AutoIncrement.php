<?php

namespace WPPluginFramework\Data\Attributes;

use Attribute;

#[Attribute]
class AutoIncrement implements IResourceFieldAttribute, IMySQLFieldFlag
{
    public function getFlagString(): string
    {
        return 'AUTO_INCREMENT';
    }
}
