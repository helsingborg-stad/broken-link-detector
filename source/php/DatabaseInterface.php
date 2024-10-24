<?php

namespace BrokenLinkDetector;

use wpdb;

interface DatabaseInterface
{
    /**
     * Get the current database version from the options table.
     * 
     * @return string|null
     */
    public function getCurrentVersion(): ?string;

    /**
     * Get the name of the table that stores broken links.
     * 
     * @return string
     */
    public function getTableName(): string;

    /**
     * Get the charset collation for the database.
     * 
     * @return string
     */
    public function getCharsetCollation(): string;

    /**
     * Get an instance of the wpdb class.
     * 
     * @return \wpdb
     */
    public static function getInstance(): wpdb;
}