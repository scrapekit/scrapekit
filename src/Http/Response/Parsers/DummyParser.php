<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Response\Parser;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;
use ScrapeKit\ScrapeKit\Http\Response\Validator;

class DummyParser extends Parser implements ResponseValidationInterface
{

    public function data()
    {
        return $this->response->body();
    }

    public function validate(): bool
    {
        return $this->response->isOk();
    }
}
