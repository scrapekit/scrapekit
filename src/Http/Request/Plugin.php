<?php

namespace ScrapeKit\ScrapeKit\Http\Request;

use ScrapeKit\ScrapeKit\Http\Request;

abstract class Plugin
{
    protected $request;

    //    /**
    //     * @var Request
    //     */
    //    public $request;
    //
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    //    abstract public function configure( Request $request );
    abstract public function configure();
}
