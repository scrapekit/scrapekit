<?php

namespace ScrapeKit\ScrapeKit\Http\Exceptions;

use Exception;
use ScrapeKit\ScrapeKit\Http\Requests\Request;
use Throwable;

class RequestException extends Exception
{

    /**
     * @var Request
     */
    protected $request;

    public function __construct($message, Request $request, $code = 0, Throwable $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, $code, $previous);
    }
}
