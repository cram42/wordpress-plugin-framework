<?php

namespace WPPluginFramework\Data\Attributes;

use Attribute;

#[Attribute]
class PrimaryKey extends MySQLConstraint implements IResourceFieldAttribute
{
    public static function getPrefix(): string
    {
        return 'PK';
    }

    public static function getType(): string
    {
        return 'PRIMARY KEY';
    }
}
