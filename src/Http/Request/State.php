<?php

namespace ScrapeKit\ScrapeKit\Http\Request;

class State
{

    const CREATED = 'created';
    const HEADERS_LOADED = 'headers_loaded';
    const BODY_LOADED = 'body_loaded';

    const SUCCESS = 'success';
    const FAIL = 'fail';
    const LAST_FAIL = 'last_fail';

    protected $currentState = self::CREATED;

    public function get()
    {
        return $this->currentState;
    }

    public function set($state)
    {
        $this->currentState = $state;
    }
}
