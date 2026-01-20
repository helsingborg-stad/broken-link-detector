<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Admin\Settings;

use AcfService\Contracts\AddOptionsPage;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddFilter;

class SanitizeLocalDomainSetting implements Hookable
{
    public function __construct(
        private addFilter&__ $wpService,
        private AddOptionsPage $acfService,
    ) {}

    public function addHooks(): void
    {
        $this->wpService->addFilter(
            'acf/update_value/key=field_6718e860b54f9',
            [$this, 'sanitizeDomainValue'],
            10,
            4,
        );
    }

    /**
     * Sanitize the domain value
     *
     * @param string $value
     * @param int $postId
     * @param array $field
     * @param string $original
     *
     * @return string
     */
    public function sanitizeDomainValue($value, $postId, $field, $original): string
    {
        $parsedUrl = parse_url($value);
        if ($parsedUrl === false || empty($parsedUrl['host'])) {
            return '';
        }
        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    }
}
