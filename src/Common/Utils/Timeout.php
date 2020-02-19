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
    protected $milliseconds;

    public function __construct(int $milliseconds)
    {

        $this->milliseconds = $milliseconds;
    }

    public function bump($milliseconds)
    {
        $this->elapsed += $milliseconds;
    }

    public function exceeded()
    {
        return $this->elapsed > $this->milliseconds;
    }
}
