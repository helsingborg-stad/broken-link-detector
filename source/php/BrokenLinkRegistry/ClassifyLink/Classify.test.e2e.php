<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use WP_UnitTestCase;

class ClassifyTest extends WP_UnitTestCase
{
  public function testRemoteGet() {
    $response = wp_remote_get('https://www.google.com/');
    $responseCode = wp_remote_retrieve_response_code($response);

    $this->assertEquals(403, $responseCode);
  }
}