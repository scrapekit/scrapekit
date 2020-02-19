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

    public function find($id)
    {
        return $this->firstWhere('id', $id);
    }
}
