<?php

namespace BrokenLinkDetector\LinkUpdater;
use WP_Post;
interface LinkUpdaterInterface
{
    public function updateLinks(int|WP_Post $post, bool $isUpdate, null|WP_Post $postBefore): void;
}