<?php

namespace BrokenLinkDetector;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterDeactivationHook;
use WpService\Contracts\RegisterActivationHook;
use WpService\Contracts\UpdateOption;
use BrokenLinkDetector\Database;
use BrokenLinkDetector\Config\Config;

class Installer implements Hookable
{
    public function __construct(
      private AddAction&RegisterActivationHook&RegisterDeactivationHook&UpdateOption $wpService, 
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

      if(!$this->isInstalled($tableName)) {

        $installSql = "CREATE TABLE IF NOT EXISTS $tableName (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          post_id bigint(20) DEFAULT NULL,
          url varchar(255) DEFAULT '' NOT NULL,
          time TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY id (id)
        ) $charsetCollation;";
        
        //Require the upgrade.php file
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //Run the dbDelta function
        dbDelta($installSql);

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
    }

    /**
     * Checks if the plugin database tables is installed
     * 
     * @return bool
     */
    private function isInstalled(string $tableName): bool
    {
      $isInstalledQuery = $this->db->getInstance()->prepare("SHOW TABLES LIKE %s", $tableName);
      if($isInstalledQuery && $this->db->getInstance()->get_var("SHOW TABLES LIKE '$tableName'") == $tableName) {
        return true; 
      }
      return false;
    }

}
