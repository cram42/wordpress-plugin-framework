<?php

namespace WPPluginFramework\Data\Attributes;

use Attribute;

#[Attribute]
class Required implements IResourceFieldAttribute, IMySQLFieldFlag
{
    public function getFlagString(): string
    {
        return 'NOT NULL';
    }
}
