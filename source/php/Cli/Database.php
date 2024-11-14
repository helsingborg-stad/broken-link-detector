<?php

namespace BrokenLinkDetector\Cli;

use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Installer;
use WpService\WpService;

class Database extends CommandCommons implements CommandInterface
{
    public function __construct(private WpService $wpService, private Config $config, private Installer $installer)
    {
    }

    public function getCommandName(): string
    {
        return 'database';
    }

    public function getCommandDescription(): string
    {
        return 'Manages the database.';
    }

    public function getCommandArguments(): array
    {
        return [
            'action' => 'Do a database action. (install, uninstall, reinstall)'
        ];
    }

    public function getCommandOptions(): array
    {
        return [];
    }

    public function getCommandHandler(): callable
    {
        return function (array $arguments, array $options) {

            $action = $arguments['action'] ?? null;

            if(!in_array($action, ['install', 'uninstall', 'reinstall'])) {
                Log::error("Invalid argument, action must be set to 'install', 'uninstall' or 'reinstall'.");
                return;
            }

            if($action == 'install') {
                $this->installer->install();
                Log::success("Database installed.");
            }

            if($action == 'uninstall') {
                $this->installer->uninstall();
                Log::success("Database uninstalled.");
                Log::warning("No database is installed: this will cause errors if the plugin is not reinstalled correctly.");
            }

            if($action == 'reinstall') {
                $this->installer->reinstall(true);
                Log::success("Database reinstalled.");
            }

        };
    }
}