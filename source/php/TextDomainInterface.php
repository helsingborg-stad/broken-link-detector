<?php

declare(strict_types=1);

namespace BrokenLinkDetector;

interface TextDomainInterface
{
    public function loadTextDomain(): void;
}
