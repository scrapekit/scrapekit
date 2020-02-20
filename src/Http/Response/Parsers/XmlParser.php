<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Response\Parser;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;

class XmlParser extends Parser implements ResponseValidationInterface
{

    public function data()
    {
        return simplexml_load_string($this->request->response()->body());
    }

    public function validate(): bool
    {
        return blackbox([ $this, 'data' ])->passes();
    }
}
