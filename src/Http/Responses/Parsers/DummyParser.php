<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Requests\RequestValidation;

class DummyParser extends Parser implements RequestValidation
{

    public function data()
    {
        return $this->response->body();
    }

    public static function validateRequest(Request $request): bool
    {
        return $request->response()->isOk();
    }
}
