<?php

namespace ScrapeKit\ScrapeKit\Http\Requests\Plugins;

use ScrapeKit\ScrapeKit\Http\Requests\Request;

class ExamplePlugin extends Plugin
{

    public function configure()
    {
        $this->request->onSuccess(function (Request $request) {
            die('Example plugin callback');
        });
    }

    /**
     * @macro
     */
    public function dump()
    {
        dump($this->request->id(), $this->request->url());
    }
}
