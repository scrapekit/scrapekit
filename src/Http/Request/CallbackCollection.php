<?php

namespace ScrapeKit\ScrapeKit\Http\Request;

use Illuminate\Support\Collection;
use ScrapeKit\ScrapeKit\Http\Request;

class CallbackCollection extends Collection
{

    public function fire(Request $request, $data = null)
    {
        //        dd($request);
        return $this->each->fire($request, $data);
    }

    //    public function reset() {
    //        return new static();
    //    }
}
