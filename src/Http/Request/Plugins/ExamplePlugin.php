<?php

namespace ScrapeKit\ScrapeKit\Http\Request\Plugins;

use ScrapeKit\ScrapeKit\Http\Request;
use ScrapeKit\ScrapeKit\Http\Request\Plugin;

class ExamplePlugin extends Plugin
{

    public function configure()
    {
        $this->request->onSuccess(function (Request $request) {
            dump('Example plugin callback');
            //            $this->dump();
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
