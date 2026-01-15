<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Cli;

abstract class CommandCommons
{
    public function getCommandHelp(): string
    {
        $usage = "Usage: {$this->getCommandName()}";

        // Add arguments to usage section
        foreach ($this->getCommandArguments() as $arg => $desc) {
            $usage .= " [--{$arg}=<{$arg}>]";
        }

        $usage .= "\n\nOptions:\n";

        // Add arguments with descriptions to options section
        foreach ($this->getCommandArguments() as $arg => $desc) {
            $usage .= "--{$arg}: {$desc}\n";
        }

        // Add options with descriptions to options section
        foreach ($this->getCommandOptions() as $opt => $desc) {
            $usage .= "--{$opt}: {$desc}\n";
        }

        return $usage;
    }

    abstract public function getCommandName(): string;

    abstract public function getCommandOptions(): array;

    abstract public function getCommandArguments(): array;
}
