<?php

namespace ScrapeKit\ScrapeKit;

use ScrapeKit\ScrapeKit\Chrome\Chrome;
use ScrapeKit\ScrapeKit\Http\Client;

class ScrapeKit
{

    /**
     * @param null $options
     *
     * @return Client
     */
    public function http($options = [])
    {
        if (is_string($options)) {
            $options = [
                'guzzle' => [
                    'base_uri' => $options,
                ],
            ];
        }

        return new Client($options);
    }

    public function chrome($url = 'http://localhost:9222')
    {
        return new Chrome($url);
    }
}
