<?php

namespace ScrapeKit\ScrapeKit\Http\Requests\Concerns;

use ScrapeKit\ScrapeKit\Http\Requests\Callbacks\RequestCallbacks;

trait SetsCallbacks
{

    public function onPartialLoad(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::BODY_PARTIALLY_LOADED, $callback);

        return $this;
    }

    public function onLastFail(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::LAST_FAIL, $callback);

        return $this;
    }

    public function onFail(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::FAIL, $callback);

        return $this;
    }

    public function onSuccess(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::SUCCESS, $callback);

        return $this;
    }

    public function onLoad(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::BODY_LOADED, $callback);

        return $this;
    }

    public function onTimeout(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::TIMEOUT, $callback);

        return $this;
    }

    public function onHeaders(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::HEADERS_LOADED, $callback);

        return $this;
    }
}
