<?php

namespace ScrapeKit\ScrapeKit\Chrome\Exceptions;

use Exception;

class ElementNotFoundException extends Exception
{


    protected $selector;

    public function __construct($selector, $code = 0, Exception $previous = null)
    {
        $this->selector = $selector;

        parent::__construct('Element ' . $this->selector . ' not found', $code, $previous);
    }
}
