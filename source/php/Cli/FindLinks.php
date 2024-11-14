<?php

namespace BrokenLinkDetector\Cli;

use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use WpService\WpService;

class FindLinks extends CommandCommons implements CommandInterface
{
    public function __construct(private WpService $wpService, private Config $config, private Database $db, private ManageRegistry $registry)
    {
    }

    public function getCommandName(): string
    {
        return 'find-links';
    }

    public function getCommandDescription(): string
    {
        return 'Collect links for a whole site.';
    }

    public function getCommandArguments(): array
    {
        return [];
    }

    public function getCommandOptions(): array
    {
        return [
            'content' => 'Optional: Search for links in content, default true.',
            'meta' => 'Optional: Search for links in meta, default true.'
        ];
    }

    public function getCommandHandler(): callable
    {
        return function (array $arguments, array $options) {

            // Default options
            $defaultOptions = [
              'content' => true,
              'meta' => true
            ];

            //Set from command options
            $options = array_merge($defaultOptions, $options);

            // Filter
            $searchIn = function(array $options): array {
              return array_keys(array_filter($options, fn($value) => $value === true));
            };
            $searchIn = $searchIn($options);
            $searchIn = implode(",", $searchIn);

            if (empty(array_filter($options))) {
                echo "No search options provided. Please provide at least one search option.\n";
                return;
            }

            Log::log("Starting link check in: {$searchIn}...");
            
            if(array_key_exists('content', $options)) {
                $this->findLinksInContent();
            }

            if(array_key_exists('meta', $options)) {  
                $this->findLinksInMeta();
            }
        };
    }

    private function findLinksInContent(): void
    {
        Log::log("Finding links in content...");

        $findLinkFromPostContent = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostContent(
            $this->wpService,
            $this->config,
            $this->db
        );

        $foundLinks = $findLinkFromPostContent->findLinks();

        Log::log("Found " . $foundLinks->getLinkCount() . " links in content.");

        $this->registry->add($foundLinks);
        
        Log::log("Links registered to database.");

    }

    private function findLinksInMeta(): void
    {
      Log::log("Finding links in post meta...");

      $findLinkFromPostMeta = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostMeta(
          $this->wpService,
          $this->config,
          $this->db
      );
      
      $foundLinks = $findLinkFromPostMeta->findLinks();
      
      Log::log("Found " . $foundLinks->getLinkCount() . " links in post meta.");
      
      $this->registry->add($foundLinks);
      
      Log::log("Links registered to database.");
    }
}