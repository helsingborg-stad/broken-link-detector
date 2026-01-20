<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Asset;

interface AssetInterface
{
    public function shouldEnqueue(): bool;

    public function getHook(): string;

    public function getHandle(): string;

    public function getFilename(): string;

    public function getDependencies(): array;

    public function getLocalizeData(): ?array;
}
