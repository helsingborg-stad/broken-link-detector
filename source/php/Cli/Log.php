<?php 

namespace BrokenLinkDetector\Cli;

use WP_CLI;
use WP_ENV;

class Log {

    private static function isCli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

    private static function isStaging(): bool
    {
        return defined('WP_ENV') && WP_ENV === 'staging';
    }

    private static function shouldLog(): bool
    {
        // Only log if CLI or in staging
        return self::isCli() || self::isStaging();
    }

    public static function success(string $message): void
    {
        if (self::isCli()) {
            \WP_CLI::success($message);
        } elseif (self::isStaging()) {
            error_log("[SUCCESS] $message");
        }
    }

    public static function error(string $message): void
    {
        if (self::isCli()) {
            \WP_CLI::error($message);
        } elseif (self::isStaging()) {
            error_log("[ERROR] $message");
        }
    }

    public static function warning(string $message): void
    {
        if (self::isCli()) {
            \WP_CLI::warning($message);
        } elseif (self::isStaging()) {
            error_log("[WARNING] $message");
        }
    }

    public static function log(string $message): void
    {
        if (self::shouldLog()) {
            if (self::isCli()) {
                \WP_CLI::log($message);
            } elseif (self::isStaging()) {
                error_log($message);
            }
        }
    }

    public static function info(string $message): void
    {
        self::log($message); 
    }

}