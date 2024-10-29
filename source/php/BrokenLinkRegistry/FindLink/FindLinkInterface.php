<?php 

namespace BrokenLinkRegistry\FindLink;

use BrokenLinkRegistry\LinkList;

interface FindLinkInterface
{
    public function findLinks(): LinkList;
    public function getHookName(): string;
    public function getHookPriority(): int;
    public function getHookAcceptedArgs(): int;
}