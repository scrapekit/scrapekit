<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Requests\RequestValidation;

class XmlParser extends Parser implements RequestValidation
{

    public function data()
    {
        return simplexml_load_string($this->request->response()->body());
    }

    public static function validateRequest(Request $request): bool
    {
        return blackbox(( [ new static($request->response()), 'data' ] ))->passes();
    }
}
