<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Response\Parser;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;

class RegexParser extends Parser
{
    

    public function all($regex)
    {

        if (preg_match_all($regex, $this->response->body(), $matches)) {
            return $matches[ 1 ];
        }

        return [];
    }

    public function first($regex)
    {

        if (preg_match($regex, $this->response->body(), $matches)) {
            return $matches[ 1 ];
        }

        return null;
    }
}
