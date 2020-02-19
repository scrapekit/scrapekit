<?php

namespace ScrapeKit\ScrapeKit;

use ScrapeKit\ScrapeKit\Chrome\Chrome;
use ScrapeKit\ScrapeKit\Http\Client;

class ScrapeKit
{

    public function http($options = null)
    {
        return new Client($options);
    }

    public function chrome($url = 'http://localhost:9222')
    {
        return new Chrome($url);
    }
}
