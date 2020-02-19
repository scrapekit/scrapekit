<?php

namespace ScrapeKit\ScrapeKit\Chrome\Concerns;

trait PausesExecution
{

    public function pause($ms)
    {
        usleep($ms * 1000);

        return $this;
    }
}
