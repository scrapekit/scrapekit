<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Response\Parser;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;

class JsonParser extends Parser implements ResponseValidationInterface
{

    public function data()
    {
        return json()->decode($this->response->body());
    }

    public function validate(): bool
    {
        return $this->data() !== null;
    }
}
