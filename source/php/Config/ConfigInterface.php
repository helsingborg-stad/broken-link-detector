<?php 

namespace BrokenLinkDetector\Config;

interface ConfigInterface
{
  public function getDatabaseVersionKey(): string;
  public function getTableName(): string;
}