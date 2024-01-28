<?php

namespace WPPluginFramework\Data\Attributes;

abstract class MySQLConstraint implements IResourceFieldAttribute
{
    public string $name;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    #region Static

    public static function getConstraintString(string $name, array $fields): string
    {
        if (empty($name)) {
            $name = implode('_', $fields);
        }

        return sprintf(
            'CONSTRAINT %s_%s %s (%s)',
            static::getPrefix(),
            $name,
            static::getType(),
            implode(', ', $fields),
        );
    }

    abstract public static function getPrefix(): string;
    abstract public static function getType(): string;

    #endregion
}
