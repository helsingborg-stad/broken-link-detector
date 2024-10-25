<?php

namespace BrokenLinkDetector\LinkUpdater;

interface LinkUpdaterInterface
{
    public function updateLinks(array $data, array $post): bool;
}