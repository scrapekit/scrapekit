<?php

namespace ScrapeKit\ScrapeKit\Http\Exceptions;

use Exception;
use ScrapeKit\ScrapeKit\Http\Request;

class RequestAbortedException extends Exception
{
    public function __construct(Request $request)
    {
        parent::__construct('Request aborted: ' . $request->url());
    }
}
