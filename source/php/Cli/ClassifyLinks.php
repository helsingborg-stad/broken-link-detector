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
    private $brokenInternalLinks = 0;
    private $brokenExternalLinks = 0;
    private $brokenLinksSummary = []; // Store broken links data for summary table

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
        return [
            'limit' => 'Optional: Limit the number of links to classify.',
        ];
    }

    public function getCommandHandler(): callable
    {
        return function (array $arguments, array $options) {
            $limit = isset($arguments['limit']) ? ((int) $arguments['limit']) : null;
            $unclassifiedLinks = $this->registry->getLinksThatNeedsClassification($limit);
            $totalNumberOfLinksToClassify = count($unclassifiedLinks);

            Log::info("Starting link classification of {$totalNumberOfLinksToClassify} links...");

            $progress = \WP_CLI\Utils\make_progress_bar("Working: ", $totalNumberOfLinksToClassify);

            foreach ($unclassifiedLinks as $link) {
                $linkObject = Link::createLink($link->url, null, $link->id, $this->wpService, $this->config);
                $linkObject->classify();

                if ($linkObject->httpCode !== null) {
                    $this->registry->update($linkObject);
                }

                $this->addToSummary($linkObject->isInternal, $linkObject->isBroken, $linkObject->httpCode, $linkObject->url);

                $progress->tick();
            }

            // Finish the progress bar
            $progress->finish();

            // Final success log
            Log::success("Link classification completed. Found {$this->brokenInternalLinks} broken internal link(s) and {$this->brokenExternalLinks} broken external link(s).");

            // Output the summary table
            $this->getSummary();
        };
    }

    /**
     * Add a link to the summary table if it's broken
     * 
     * @param bool $isInternal
     * @param bool $isBroken
     * @param int|null $httpCode
     * @param string $url
     * 
     * @return void
     */
    private function addToSummary(bool $isInternal, bool $isBroken, ?int $httpCode, string $url): void {
        if ($isBroken) {
            $this->brokenLinksSummary[] = [
                'url' => $url,
                'httpCode' => $httpCode,
                'internal' => ($isInternal ? 'Internal' : 'External')
            ];
        }

        if ($isInternal && $isBroken) {
            $this->brokenInternalLinks++;
        } elseif (!$isInternal && $isBroken) {
            $this->brokenExternalLinks++;
        }
    }

    /**
     * Output the summary table of broken links
     * 
     * @return void
     */
    private function getSummary(): void {
        \WP_CLI\Utils\format_items('table', $this->brokenLinksSummary, ['url', 'httpCode', 'internal']);
    }
}