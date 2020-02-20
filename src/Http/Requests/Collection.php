<?php

namespace ScrapeKit\ScrapeKit\Http\Requests;

use Illuminate\Support\Collection as IlluminateCollection;

class Collection extends IlluminateCollection
{

    /**
     * The items contained in the collection.
     *
     * @var Request[]
     */
    protected $items = [];

    public function unprocessed()
    {

        return $this->filter(function (Request $request) {
            return $request->state()->is(State::NEW);
        });
    }
}
