<?php

declare(strict_types=1);

namespace BrokenLinkDetector;

interface InstallerInterface
{
    public function install(): void;

    public function uninstall(): void;

    public function reinstall(): void;
}
