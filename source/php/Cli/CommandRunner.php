<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Cli;

use BrokenLinkDetector\Config\Config;
use WP_CLI;
use WpService\WpService;

class CommandRunner
{
    private array $commands = [];

    /**
     * CommandRunner constructor.
     */
    public function __construct(
        private WpService $wpService,
        private Config $config,
    ) {}

    /**
     * Add a command to the command runner.
     */
    public function addCommand(CommandInterface $command): self
    {
        $this->commands[$command->getCommandName()] = $command;
        return $this;
    }

    /**
     * Get all registered commands.
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Run a command with the given arguments and options.
     */
    public function runCommand(string $commandName, array $arguments, array $options): void
    {
        if (!isset($this->commands[$commandName])) {
            throw new \Exception('Command not found');
        }

        $command = $this->commands[$commandName];
        $handler = $command->getCommandHandler();
        $handler($arguments, $options);
    }

    /**
     * Register each command with WP-CLI.
     */
    public function registerWithWPCLI(): bool
    {
        if (!defined('WP_CLI') || defined('WP_CLI') && !WP_CLI) {
            return false;
        }

        foreach ($this->commands as $commandName => $command) {
            WP_CLI::add_command(
                "{$this->config->getCommandNamespace()} {$commandName}",
                static function ($options, $arguments) use ($command) {
                    $handler = $command->getCommandHandler();
                    $handler($arguments, $options);
                },
                [
                    'shortdesc' => $command->getCommandDescription(),
                    'synopsis' => $this->generateSynopsis($command),
                ],
            );
        }

        return true;
    }

    /**
     * Generate the synopsis for a command.
     */
    private function generateSynopsis(CommandInterface $command): array
    {
        $synopsis = [];
        foreach ($command->getCommandArguments() as $arg => $desc) {
            $synopsis[] = [
                'type' => 'assoc',
                'name' => $arg,
                'description' => $desc,
                'optional' => false,
            ];
        }
        foreach ($command->getCommandOptions() as $opt => $desc) {
            $synopsis[] = [
                'type' => 'assoc',
                'name' => $opt,
                'description' => $desc,
                'optional' => true,
            ];
        }
        return $synopsis;
    }
}
