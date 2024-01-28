<?php

namespace WPPluginFramework\Data\Attributes;

use Attribute;

#[Attribute]
class Unique extends MySQLConstraint implements IResourceFieldAttribute
{
    public static function getPrefix(): string
    {
        return 'UC';
    }

    public static function getType(): string
    {
        return 'UNIQUE';
    }
}
