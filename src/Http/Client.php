<?php

namespace ScrapeKit\ScrapeKit\Http;

use Exception;
use GuzzleHttp\Promise\CancellationException;
use ScrapeKit\ScrapeKit\Http\Request\RequestCollection;
use Throwable;

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
    protected $promises = [];
    protected $promise;

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

    /**
     * @param $urlOrRequest
     *
     * @return Response
     * @throws Exception
     */
    public function request($urlOrRequest)
    {
        $req = Request::make($urlOrRequest);
        $this->addRequest($req)->run();

        return $req->response();
    }

    public function run()
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

            $this->promise = ( new \GuzzleHttp\Promise\EachPromise($fnc(), [ 'concurrency' => $this->threads, ]) )->promise();
            $this->promise->wait();
        }

        return $this;
    }

    public function stop($e)
    {

        try {
            $this->promise->reject($e);
        } catch (CancellationException $ee) {
        }

        return $this;
    }

    public function throw($e)
    {

        $this->stop($e);
    }
}
