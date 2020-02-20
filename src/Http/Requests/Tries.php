<?php

namespace ScrapeKit\ScrapeKit\Http\Requests;

class Tries
{

    protected $current = 0;
    protected $max = 1;

    /**
     * Tries constructor.
     *
     * @param int $current
     * @param int $max
     */
    public function __construct(int $current = 0, int $max = 1)
    {
        $this->current = $current;
        $this->max     = $max;
    }


    public function max(int $value = null)
    {
        if ($value !== null) {
            $this->max = $value;
        }

        return $this->max;
    }

    public function current(int $value = null)
    {
        if ($value !== null) {
            $this->current = $value;
        }

        return $this->current;
    }

    public function exceeded()
    {
        return $this->current() >= $this->max();
    }

    public function increment($value = 1)
    {
        $this->current += $value;
    }
}
