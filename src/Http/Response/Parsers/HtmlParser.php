<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use DiDom\Document;
use ScrapeKit\ScrapeKit\Http\Response\Parser;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;

class HtmlParser extends Parser implements ResponseValidationInterface
{

    public function data()
    {

        return new Document($this->response->body());
        //        return json()->decode( $this->response->body() );
    }

    public function validate(): bool
    {
        return blackbox([ $this, 'data' ])->passes();
    }
}
