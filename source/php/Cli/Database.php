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
        return [];
    }

    public function getCommandOptions(): array
    {
        return [
            'install' => 'Install the database.',
            'uninstall' => 'Uninstall the database.',
            'reinstall' => 'Reinstall the database (uninstall, install).'
        ];
    }

    public function getCommandHandler(): callable
    {
        return function (array $arguments, array $options) {

            var_dump($options);

            if(count($options) != 1) {
                Log::error("Invalid number of arguments, only a single argument is allowed.");
                return;
            }

            if(!in_array($options[0], ['install', 'uninstall', 'reinstall'])) {
                Log::error("Invalid argument, only 'install', 'uninstall' or 'reinstall' is allowed.");
                return;
            }
            
            if($options[0] == 'install') {
                $this->installer->install();
                Log::log("Database installed.");
            }

            if($options[0] == 'uninstall') {
                $this->installer->uninstall();
                Log::log("Database uninstalled.");
            }

            if($options[0] == 'reinstall') {
                $this->installer->reinstall(true);
                Log::log("Database reinstalled.");
            }

        };
    }
}