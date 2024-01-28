<?php

namespace WPPluginFramework\Data\Attributes;

use Attribute;

#[Attribute]
class Length implements IResourceFieldAttribute
{
    public int $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }
}
