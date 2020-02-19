<?php

namespace ScrapeKit\ScrapeKit\Http;

use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use ScrapeKit\ScrapeKit\Http\Guzzle\Middleware\Retry;
use ScrapeKit\ScrapeKit\Http\Request;
use ScrapeKit\ScrapeKit\Http\Request\RequestCollection;

use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;

class Client
{

    /**
     * @var RequestCollection
     */
    public $requests;
    public $options = [ 'guzzle' => [ 'concurrency' => 10 ] ];
    /**
     * @var array
     */
    private $promises = [];

    public function __construct($options = [])
    {
        $this->requests = new RequestCollection();

        $this->options = array_replace($this->options, $options);

        return $this;
    }

    public function run()
    {
        return $this->runAsync()->wait();
    }


    /**
     * @param $requests
     *
     * @return $this
     */
    public function addRequests($requests)
    {
        $this->requests = $this->requests->merge($requests);
        $this->requests->each->client($this);

        return $this;
    }

    public function addRequest(Request $request)
    {
        $this->addRequests([ $request ]);

        return $this;
    }

    public function runAsync()
    {

        $this->promises = [];

        $stack = new HandlerStack();
        $stack->setHandler(new CurlMultiHandler());
        $stack->push(Middleware::redirect(), 'allow_redirects');
        $stack->push(Middleware::cookies(), 'cookies');
        $stack->push(Middleware::prepareBody(), 'prepare_body');
        $stack->push(Middleware::httpErrors(), 'http_errors');
        $stack->push(Retry::factory());

        $guzzle = new \GuzzleHttp\Client(array_replace($this->options[ 'guzzle' ], [ 'handler' => $stack ]));

        /** @var Request $request */
        foreach ($this->requests as $request) {
            $promise = $request->send($guzzle);

            $this->promises [ $request->id() ] = $promise;
        }

        return $this;
    }

    public function wait($errors = 1)
    {
        if ($errors) {
            unwrap($this->promises);
        } else {
            settle($this->promises)->wait();
        }

        return $this->requests->map->response()->toArray();
    }


    //
    //    public function batch($requests)
    //    {
    //        if ($requests instanceof RequestCollection) {
    //            return $this->executeRequests($requests);
    //        }
    //
    //        foreach ($requests as &$r) {
    //            $r = Request::wrap($r);
    //        }
    //
    //        return $this->executeRequests(RequestCollection::wrap($requests));
    //    }
    //
    //    public function request($method, $url = null, $options = [])
    //    {
    //
    //        if ($method && $url) {
    //            return $this->executeRequests(RequestCollection::wrap([ new Request($method, $url, $options) ]), true);
    //        }
    //
    //        if ($method instanceof Request) {
    //            return $this->executeRequests(RequestCollection::wrap([ $method ]), true);
    //        }
    //
    //        // = request('http://httpbin.org')
    //        if (is_string($method)) {
    //            $method = [ 'GET', $method ];
    //        }
    //
    //        if (is_array($method)) {
    //            return $this->executeRequests(RequestCollection::wrap([ Request::wrap($method) ]), true);
    //        }
    //
    //        throw new \InvalidArgumentException('Could not create request(s) from the input provided');
    //    }
}
