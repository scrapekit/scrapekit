<?php

namespace ScrapeKit\ScrapeKit\Http\Request;

use Illuminate\Support\Arr;

class RequestOptions
{

    protected $options = [

        'on' => [
            'headers_loaded'        => null,
            'body_partially_loaded' => null,
            'body_loaded'           => null,
            'success'               => null,
            'fail'                  => null,
            'last_fail'             => null,
            'timeout'               => null,
        ],

        'timeout' => 0,

        'guzzle' => [],
    ];

    public function __construct($options = [])
    {

        $this->options = array_replace($options);
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
    }
}
