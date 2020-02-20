<?php

namespace ScrapeKit\ScrapeKit\Http\Requests\Plugins;

use ScrapeKit\ScrapeKit\Http\Requests\Request;

abstract class Plugin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract public function configure();
}
