<?php

namespace BrokenLinkDetector;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterDeactivationHook;
use WpService\Contracts\RegisterActivationHook;
use WpService\Contracts\UpdateOption;
use WpService\Contracts\DeleteOption;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;

class Installer implements InstallerInterface, Hookable
{
    public function __construct(
      private AddAction&RegisterActivationHook&RegisterDeactivationHook&UpdateOption&DeleteOption $wpService, 
      private Config $config, 
      private Database $db
    ){
      if (!file_exists($config->getPluginPath())) {
        throw new \InvalidArgumentException('The plugin path provided does not exist');
      }
    }

    /**
     * Registeres the activation and deactivation hooks
     * 
     * @return void
     */
    public function addHooks(): void
    {
      $this->wpService->registerActivationHook($this->config->getPluginPath(), array($this, 'install'));
      $this->wpService->registerDeactivationHook($this->config->getPluginPath(), array($this, 'uninstall'));

      // Run install when the options page is saved acf. 
      $this->wpService->addAction('acf/save_post', array($this, 'install'));
    }

    /**
     * Runs the installation process
     * 
     * @return void
     */
    public function install(): void
    {
      $charsetCollation = $this->db->getCharsetCollation();
      $tableName        = $this->db->getTableName();

      //Delete the database, if the current 
      //version is not the same as the configured version
      //The data in the database will be lost.
      //This behavious is deemed acceptable, as 
      //the table will be repopulated at the next run. 
      if(!$this->isCurrentDatabaseVersion()) {
        $this->uninstall();
      }

      if(!$this->isInstalled($tableName)) {
        $installSql = "CREATE TABLE IF NOT EXISTS $tableName (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          post_id bigint(20) DEFAULT NULL,
          url varchar(1024) NOT NULL DEFAULT '',
          unique_hash char(32) NOT NULL,
          time TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          UNIQUE KEY unique_post_url_hash (unique_hash)
        ) $charsetCollation;";
                
        //Require the upgrade.php file
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //Run the dbDelta function
        dbDelta($installSql);

        //Save the current database version
        $this->wpService->updateOption(
          $this->config->getDatabaseVersionKey(), 
          $this->config->getDatabaseVersion()
        );
      }
    }

    /**
     * Runs the uninstallation process
     * 
     * @return void
     */
    public function uninstall(): void
    {
        $this->db->getInstance()->query("DROP TABLE IF EXISTS " . $this->db->getTableName());
        $this->wpService->deleteOption($this->config->getDatabaseVersionKey());
    }

    /**
     * Reinstalls the plugin, if needed
     * 
     * @return void
     */
    public function reinstall($force = true): void
    {
      if($force) {
        $this->uninstall();
      }
      $this->install();
    }

    /**
     * Checks if the plugin database tables is installed
     * 
     * @return bool
     */
    private function isInstalled(string $tableName): bool
    {
      $isInstalledQuery = $this->db->getInstance()->prepare("SHOW TABLES LIKE %s", $tableName);
      if($isInstalledQuery && $isInstalledQuery == $tableName) {
        return true; 
      }
      return false;
    }

    /**
     * Checks if the current database version is the same as the confgured database version
     * 
     * @return bool
     */
    private function isCurrentDatabaseVersion(): bool
    {
      return $this->db->getCurrentVersion() === $this->config->getDatabaseVersion();
    }

}
