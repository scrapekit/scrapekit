<?php

namespace ScrapeKit\ScrapeKit\Http\Request;

class State
{

    public const NEW = 'new';
    public const PROCESSING = 'processing';
    public const FINISHED = 'finished';

    protected $currentState = self::NEW;

    public function get()
    {
        return $this->currentState;
    }

    public function is($state)
    {
        return $this->get() === $state;
    }

    public function set($state)
    {
        $this->currentState = $state;
    }
}
