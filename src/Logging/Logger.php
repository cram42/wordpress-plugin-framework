<?php

namespace WPPluginFramework;

require_once 'LogLevel.php';

abstract class Logger
{
    private static array $source_levels = array();

    public static function setLevel(string $source, $level): void
    {
        static::$source_levels[$source] = $level;
    }

    public static function log(string $message, string $source = '', string $display = '', $level = LogLevel::INFO): string
    {
        $filter_level = isset(static::$source_levels[$display])
            ? static::$source_levels[$display]
            : (
                isset(static::$source_levels[$source])
                    ? static::$source_levels[$source]
                    : LogLevel::DEFAULT
            );

        $output = LogLevel::getText($level) . ' | ';
        $output .= !empty($display)
            ? $display
            : $source;
        $output .= ' | ';
        $output .= $message;

        if ($level <= $filter_level) {
            error_log($output);
        }

        return $output;
    }

    public static function error(string $message, string $source = '', string $display = ''): string
    {
        return static::log($message, $source, $display, LogLevel::ERROR);
    }

    public static function warning(string $message, string $source = '', string $display = ''): string
    {
        return static::log($message, $source, $display, LogLevel::WARNING);
    }

    public static function info(string $message, string $source = '', string $display = ''): string
    {
        return static::log($message, $source, $display, LogLevel::INFO);
    }

    public static function debug(string $message, string $source = '', string $display = ''): string
    {
        return static::log($message, $source, $display, LogLevel::DEBUG);
    }
}
