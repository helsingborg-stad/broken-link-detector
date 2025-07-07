<?php

namespace AcfService\Contracts;

interface GetField {
    public function getField($field, $post_id = false);
}

interface AddOptionsPage {
    public function addOptionsPage($args);
}