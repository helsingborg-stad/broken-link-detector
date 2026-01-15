<?php

declare(strict_types=1);

namespace BrokenLinkDetector\LinkUpdater;

use WP_Post;

interface LinkUpdaterInterface
{
    public function updateLinks(int $postId, WP_Post $postBefore, WP_Post $postAfter): void;
}
