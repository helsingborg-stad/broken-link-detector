<?php 

namespace BrokenLinkDetector;

interface InstallerInterface
{
  public function install(): void;
  public function uninstall(): void;
  public function reinstall(): void;
}