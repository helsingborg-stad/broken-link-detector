<?php 

namespace BrokenLinkDetector\Cli;

class Log {

  public static function success(string $message): void
  {
      \WP_CLI::success($message);
  } 

  public static function error(string $message): void
  {
      \WP_CLI::error($message);
  }

  public static function log(string $message): void
  {
      \WP_CLI::log($message);
  }

}