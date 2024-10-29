<?php 

namespace BrokenLinkDetector;

interface LinkResolverInterface 
{
    public function getLinks($resolver = null): string;
}