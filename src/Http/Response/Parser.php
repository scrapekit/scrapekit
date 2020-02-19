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

    abstract public function data();
}
