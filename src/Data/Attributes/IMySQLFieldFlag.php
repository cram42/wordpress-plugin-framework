<?php

namespace WPPluginFramework\Data\Attributes;

interface IMySQLFieldFlag
{
    public function getFlagString(): string;
}