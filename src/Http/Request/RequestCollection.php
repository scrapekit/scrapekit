<?php

namespace ScrapeKit\ScrapeKit\Http\Request;

use Illuminate\Support\Collection;

class RequestCollection extends Collection
{

    /**
     * The items contained in the collection.
     *
     * @var Request[]
     */
    protected $items = [];

    public function unprocessed()
    {

        return $this->filter(function ($request) {
            return $request->state()->is(State::NEW);
        });
    }
}
