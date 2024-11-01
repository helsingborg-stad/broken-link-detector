<?php

namespace BrokenLinkDetector\Cli;

use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Installer;
use WpService\WpService;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;


class ClassifyLinks extends CommandCommons implements CommandInterface
{
    public function __construct(private WpService $wpService, private Config $config, private Database $database, private ManageRegistry $registry)
    {
    }

    public function getCommandName(): string
    {
        return 'classify-links';
    }

    public function getCommandDescription(): string
    {
        return 'Link classification.';
    }

    public function getCommandArguments(): array
    {
        return [];
    }

    public function getCommandOptions(): array
    {
        return [];
    }

    public function getCommandHandler(): callable
    {
        return function (array $arguments, array $options) {
            
          $unclassifiedLinks = $this->registry->getUnclassifiedLinks();

          foreach($unclassifiedLinks as $link) {
    
            $linkObject = Link::createLink($link->url, null, $link->id, $this->wpService, $this->config);
            $linkObject->classify();

            //Log the classification
            $status       = $linkObject->isInternal ? 'internal' : 'external';
            $brokenStatus = $linkObject->isBroken ? 'broken' : 'not broken';

            Log::log("Classifying link: " . $link->url . " as {$status} and {$brokenStatus}.");

            // Store the classification
            if($linkObject->httpCode != null) {
              $this->registry->update(
                $linkObject
              );
            } 
          }

          Log::success("Link classification has been made.");
        };
    }
}