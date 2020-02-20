<?php

namespace ScrapeKit\ScrapeKit\Http\Responses\Parsers;

use DiDom\Document;
use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Requests\RequestValidation;

class HtmlParser extends Parser implements RequestValidation
{

    public function data()
    {

        return new Document($this->response->body());
    }

    public static function validateRequest(Request $request): bool
    {
        return blackbox(( [ new static($request->response()), 'data' ] ))->passes();
    }
}
