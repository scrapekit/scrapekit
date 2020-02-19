<?php

namespace ScrapeKit\ScrapeKit\Http;

use Illuminate\Support\Arr;
use ScrapeKit\ScrapeKit\Http\Request\RequestCollection;

class Client
{

    public function __construct()
    {
    }


    /**
     * @param RequestCollection $requests
     */
    protected function executeRequests($requests, $single = false)
    {
        $responses = $this->executeRequestsAsync($requests)->wait();

        return $single ? $responses[ 0 ] : $responses;
    }

    protected function executeRequestsAsync($requests)
    {

        $handler = new GuzzleHandler($requests);
        $handler->start();

        return $handler;
    }

    public function batch($requests)
    {
        if ($requests instanceof RequestCollection) {
            return $this->executeRequests($requests);
        }

        foreach ($requests as &$r) {
            $r = Request::wrap($r);
        }

        return $this->executeRequests(RequestCollection::wrap($requests));
    }

    public function request($method, $url = null, $options = [])
    {

        if ($method && $url) {
            return $this->executeRequests(RequestCollection::wrap([ new Request($method, $url, $options) ]), true);
        }

        if ($method instanceof Request) {
            return $this->executeRequests(RequestCollection::wrap([ $method ]), true);
        }

        // = request('http://httpbin.org')
        if (is_string($method)) {
            $method = [ 'GET', $method ];
        }

        if (is_array($method)) {
            return $this->executeRequests(RequestCollection::wrap([ Request::wrap($method) ]), true);
        }

        throw new \InvalidArgumentException('Could not create request(s) from the input provided');
    }
}
