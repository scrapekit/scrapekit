<?php

namespace ScrapeKit\ScrapeKit\Http\Requests\Callbacks;

use ScrapeKit\ScrapeKit\Http\Requests\Request;

class Callback
{
    /**
     * @var callable
     */
    protected $handler;

    /**
     * Callback constructor.
     *
     * @param callable $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function fire(Request $request, $data = null)
    {
        $callback = $this->handler;

        return $callback($request, $data);
    }
}
