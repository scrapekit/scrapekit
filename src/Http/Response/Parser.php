<?php

namespace ScrapeKit\ScrapeKit\Http\Response;

use ScrapeKit\ScrapeKit\Http\Response;

abstract class Parser
{

    /**
     * @var Response
     */
    protected $response;

    public function __construct(Response $response)
    {

        $this->response = $response;
    }

    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->response->{$property};
    }

    public function __call($method, $args)
    {
        return call_user_func([ $this->response, $method ], $args);
    }
}
