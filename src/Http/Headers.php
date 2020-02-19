<?php

namespace ScrapeKit\ScrapeKit\Http;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Headers implements Arrayable
{

    /**
     * @var Collection
     */
    protected $headers;

    public function __construct(array $headers)
    {
        $this->headers = collect($headers);
    }

    public function get($header, $default = [])
    {
        return $this->headers->filter(function ($v, $k) use ($header) {
            return mb_convert_case($k, MB_CASE_LOWER) == mb_convert_case($header, MB_CASE_LOWER);
        })->first() ?: $default;
    }

    public function first($header, $default = [])
    {
        return collect($this->headers->get($header, $default))->first();
    }

    public function all()
    {
        return $this->headers->toArray();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->headers->toArray();
    }
}
