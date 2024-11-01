<?php 

namespace BrokenLinkDetector\Cli; 

use WP_CLI;

class CommandRunner
{
    private string $commandPrefix = 'broken-link-detector'; // Move to conf

    private array $commands = [];

    public function addCommand(CommandInterface $command): self
    {
        $this->commands[$command->getCommandName()] = $command;
        return $this;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function runCommand(string $commandName, array $arguments, array $options): void
    {
        if (!isset($this->commands[$commandName])) {
            throw new \Exception("Command not found");
        }

        var_dump($arguments);

        $command = $this->commands[$commandName];
        $handler = $command->getCommandHandler();
        $handler($arguments, $options);
    }

    /**
     * Register each command with WP-CLI.
     */
    public function registerWithWPCLI(): void
    {
        foreach ($this->commands as $commandName => $command) {
          WP_CLI::add_command("{$this->commandPrefix} {$commandName}", function ($options, $arguments) use ($command) {
            $handler = $command->getCommandHandler();
            $handler($arguments, $options);
        }, [
            'shortdesc' => $command->getCommandDescription(),
            'synopsis' => $this->generateSynopsis($command)
        ]);
      }
    }

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