<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Config;

use AcfService\AcfService;
use WpService\WpService;

interface ConfigInterface
{
    public function __construct(
        WpService $wpService,
        AcfService $acfService,
        string $filterPrefix,
        string $pluginPath,
        string $pluginUrl,
    );

    public function getDatabaseVersionKey(): string;

    public function getDatabaseVersion(): string;

    public function getTableName(): string;

    public function getPluginUrl(): string;

    public function getPluginPath(bool $relative): string;

    public function getPluginFieldsPath(): string;

    public function getTextDomain(): string;

    public function linkUpdaterBannedPostTypes(): array;

    public function linkDetectBannedPostTypes(): array;

    public function linkDetectAllowedPostStatuses(): array;

    /* Link classification */
    public function responseCodesConsideredBroken(): array;

    public function getRecheckInterval(): int;

    /* DNS lookup */
    public function checkIfDnsRespondsBeforeProbingUrl(): bool;

    /* Http polling */
    public function getMaxRedirects(): int;

    public function getTimeout(): int;

    /* Context detection */
    public function getDomainsThatShouldNotBeChecked(): array;

    public function isContextCheckEnabled(): bool;

    public function getContextCheckUrl(): string;

    public function getContextCheckTimeout(): int;

    public function getContextCheckDomainsToDisable(): array;

    public function getContextCheckSuccessClass(): string;

    public function getContextCheckFailedClass(): string;

    public function getContextCheckTooltipText(): string;

    /* Modal */
    public function getTemplateDirectory(): string;

    public function getContextCheckIsModalActive(): bool;

    public function getContextCheckIsToolTipActive(): bool;

    public function getContextCheckModalTitle(): string;

    public function getContextCheckModalContent(): string;
}
