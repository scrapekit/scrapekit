<?php

namespace ScrapeKit\ScrapeKit\Http;

use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\CancellationException;
use GuzzleHttp\Promise\EachPromise;
use ScrapeKit\ScrapeKit\Http\Guzzle\Middleware\Retry;
use ScrapeKit\ScrapeKit\Http\Request\RequestCollection;

use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;

class Client
{

    /**
     * @var RequestCollection
     */
    public $requests;
    public $options = [];
    public $threads = 10;
    /**
     * @var array
     */
    private $promises = [];

    /**
     * Client constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->requests = new RequestCollection();
        $this->options  = array_replace($this->options, $options);
    }

    public function threads($num = null)
    {
        if ($num !== null) {
            $this->threads = $num;
        }

        return $this->threads;
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

    public function request($urlOrRequest)
    {
        $req = Request::make($urlOrRequest);
        $this->addRequest($req)->handle();

        return $req->response();
    }

    protected function handle()
    {

        $guzzle = new \GuzzleHttp\Client([
            'expect'      => false,
            'http_errors' => false,
        ]);

        while ($count = $this->requests->unprocessed()->count()) {
            dump('Unprocessed requests: ' . $count);
            $fnc = function () use ($guzzle) {
                while ($r = $this->requests->unprocessed()->first()) {
                    yield $r->send($guzzle);
                }
            };

            $this->prom = ( new \GuzzleHttp\Promise\EachPromise($fnc(), [ 'concurrency' => $this->threads, ]) )->promise();

            $this->prom->wait();
        }

        return $this;
    }

    public function throw($e)
    {
        try {
            $this->prom->reject($e);
        } catch (CancellationException $ee) {
        }
        throw $e;
    }
    //
    //    public function wait( $errors = 0 ) {
    //        if ( $errors ) {
    //            unwrap( $this->promises );
    //        } else {
    //            settle( $this->promises )->wait();
    //        }
    //
    //        return $this->requests->map->response()->toArray();
    //    }
}
