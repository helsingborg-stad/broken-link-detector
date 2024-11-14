<?php

namespace BrokenLinkDetector\Database;

use BrokenLinkDetector\Config\ConfigInterface;
use wpdb;
use WpService\Contracts\GetOption;

class Database implements DatabaseInterface
{

    public function __construct(private ConfigInterface $config, private GetOption $wpService)
    {
    }

    /**
     * Get the current version of the database.
     * 
     * @return string|null
     */
    public function getCurrentVersion(): ?string
    {
        return $this->wpService->getOption(
          $this->config->getDatabaseVersionKey(),
          null
        );
    }

    /**
     * The database table that broken link checker registeres broken stuff in. 
     * 
     * @return string
     */
    public function getTableName(): string
    {
        return self::getInstance()->prefix . $this->config->getTableName();
    }

    /**
     * Get the charset collation for the database.
     * 
     * @return stringxx
     */
    public function getCharsetCollation(): string
    {
        return self::getInstance()->get_charset_collate();
    }

    /**
     * @return \wpdb
     */
    public static function getInstance(): wpdb
    {
      static $db; 
      if ($db === null) {
        global $wpdb;
        $db = $wpdb;
      }
      return $db;
    }
}