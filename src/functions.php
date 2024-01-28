<?php

namespace WPPluginFramework;

use WPPluginFramework\Logger;

function getPropertiesSorted(\ReflectionClass $reflector): array
{
    $properties = $reflector->getProperties();
    $classes_sorted = array_reverse(
        array_unique(
            array_map(fn ($property) => $property->class, $properties)
        )
    );

    $properties_sorted = [];
    foreach ($classes_sorted as $class) {
        foreach ($properties as $property) {
            if ($property->class == $class) {
                $properties_sorted[] = $property;
            }
        }
    }
    return $properties_sorted;
}

function strprecmp(string $string, string $prefix)
{
    return strncmp($string, $prefix, strlen($prefix));
}

function strsuffix(string $string, string $splitter, bool $include_splitter = false)
{
    $suffix_pos = strrpos($string, $splitter);
    $suffix_pos += $include_splitter ? 0 : strlen($splitter);
    return substr($string, $suffix_pos);
}

function typePHPtoREST(string $php_type): string
{
    switch ($php_type) {
        case 'int':
            return 'integer';
        case 'string':
            return 'string';
        case 'bool':
            return 'boolean';
        case 'float':
            return 'float';
        default:
            $error = sprintf('typePHPtoREST(): Unimplemented data type "%s"', $php_type);
            Logger::error($error, 'functions.php');
            throw new \Exception($error);
    }
}

function typePHPtoMySQL(string $php_type, int|null $max_string_size = null): string
{
    $max_string_size ??= 255;

    switch ($php_type) {
        case 'int':
            return 'INTEGER';
        case 'string':
            return sprintf('VARCHAR(%s)', $max_string_size);
        case 'bool':
            return 'BOOLEAN';
        case 'float':
            return 'FLOAT';
        default:
            $error = sprintf('typePHPtoMySQL(): Unimplemented data type "%s"', $php_type);
            Logger::error($error, 'functions.php');
            throw new \Exception($error);
    }
}
