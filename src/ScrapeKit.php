<?php

namespace ScrapeKit\ScrapeKit;

use ScrapeKit\ScrapeKit\Chrome\Chrome;
use ScrapeKit\ScrapeKit\Http\Client;

class ScrapeKit
{

    /**
     *
     * @param $url
     *
     * @return Client
     */
    public function http($url = null)
    {

        return new Client($url);
    }

    public function chrome($url = 'http://localhost:9222')
    {
        return new Chrome($url);
    }
}
