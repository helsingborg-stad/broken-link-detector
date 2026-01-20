<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\FindLink;

use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkInterface;
use BrokenLinkDetector\HooksRegistrar\Hookable;

class FindLink implements Hookable
{
    private array $findLinkResolvers;

    public function __construct(
        private $wpService,
        FindLinkInterface ...$findLink,
    ) {
        $this->findLinkResolvers = $findLink;
    }

    public function addHooks(): void
    {
        if (empty($this->findLinkResolvers)) {
            throw new \InvalidArgumentException('No find link resolvers provided');
        }

        foreach ($this->findLinkResolvers as $resolver) {
            $this->wpService->addAction(
                $resolver->getHookName(),
                array($resolver, 'findLinks'),
                $resolver->getHookPriority(),
                $resolver->getHookAcceptedArgs(),
            );
        }
    }
}
