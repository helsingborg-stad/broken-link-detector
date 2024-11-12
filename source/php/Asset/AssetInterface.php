<?php 

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Config\Config;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpLocalizeScript;
use WpService\Contracts\WpRegisterScript;
use WpService\Contracts\WpRegisterStyle;

interface AssetInterface
{
  public function getHook(): string;
  public function getHandle(): string;
  public function getFilename(): string;
  public function getDependencies(): array;
  public function getLocalizeData(): ?array;
}