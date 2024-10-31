<?php 

namespace BrokenLinkDetector\Cli;

interface CommandInterface
{
    public function getCommandName(): string;
    public function getCommandDescription(): string;
    public function getCommandHelp(): string;
    public function getCommandArguments(): array;
    public function getCommandOptions(): array;
    public function getCommandHandler(): callable;
} 