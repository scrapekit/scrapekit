<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Responses\Parsers\Concerns\ResponseValidationInterface;

class JsonParser extends Parser implements RequestValidation
{

    public function data()
    {
        return json()->decode($this->response->body());
    }

    public static function validateRequest(Request $request): bool
    {
        return ( new static($request->response()) )->data() !== null;
    }
}
