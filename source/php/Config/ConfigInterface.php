<?php 

namespace BrokenLinkDetector\Config;

interface ConfigInterface
{
  public function getDatabaseVersionKey(): string;
  public function getTableName(): string;
  public function getPluginUrl(): string;
  public function getPluginPath(): string;
  public function getPluginTemplatePath(): string;
  public function getTextDomain(): string;
}