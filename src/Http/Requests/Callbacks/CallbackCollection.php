<?php

namespace ScrapeKit\ScrapeKit\Http\Requests\Callbacks;

use Illuminate\Support\Collection;
use ScrapeKit\ScrapeKit\Http\Requests\Request;

class CallbackCollection extends Collection
{

    public function fire(Request $request, $data = null)
    {
        return $this->each->fire($request, $data);
    }
}
