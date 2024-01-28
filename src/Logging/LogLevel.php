<?php

namespace WPPluginFramework;

enum LogLevel: int
{
    public const ERROR = 0;
    public const WARNING = 1;
    public const INFO = 2;
    public const DEBUG = 3;

    public const DEFAULT = LogLevel::INFO;

    public static function getText($level): string
    {
        return match ($level) {
            LogLevel::ERROR => 'ERROR',
            LogLevel::WARNING => 'WARNING',
            LogLevel::INFO => 'INFO',
            LogLevel::DEBUG => 'DEBUG',
        };
    }
}
