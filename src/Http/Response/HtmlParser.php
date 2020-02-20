<?php

namespace ScrapeKit\ScrapeKit\Http\Response;

use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;

class HtmlParser extends Parser
{

    public function data()
    {
        //        return json()->decode( $this->response->body() );
    }

    //    public function validate(): bool {
    //        return $this->data() !== null;
    //
    //    }
}
