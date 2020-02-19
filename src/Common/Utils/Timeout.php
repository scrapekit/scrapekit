<?php

namespace ScrapeKit\ScrapeKit\Common\Utils;

class Timeout
{

    /**
     * @var int
     */
    protected $elapsed = 0;

    /**
     * @var int
     */
    protected $miliseconds;

    public function __construct(int $miliseconds)
    {

        $this->miliseconds = $miliseconds;
    }

    public function bump($miliseconds)
    {
        $this->elapsed += $miliseconds;
    }

    public function exceeded()
    {
        return $this->elapsed > $this->miliseconds;
    }
}
