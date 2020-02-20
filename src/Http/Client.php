<?php

namespace ScrapeKit\ScrapeKit\Http;

use Exception;
use GuzzleHttp\Promise\CancellationException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\Promise;
use ScrapeKit\ScrapeKit\Http\Requests\Collection;
use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Responses\Response;

class Client
{

    /**
     * @var Collection
     */
    public $requests;
    public $options = [];
    /**
     * @var int
     */
    public $threads = 10;
    /**
     * @var array
     */
    protected $promises = [];
    /**
     * @var Promise
     */
    protected $promise;

    /**
     * Client constructor.
     *
     * @param $url
     */
    public function __construct($url = null)
    {
        $this->requests                          = new Collection();
        $this->options[ 'guzzle' ][ 'base_uri' ] = $url;
    }

    public function threads($num = null)
    {
        if ($num !== null) {
            $this->threads = $num;

            return $this;
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
            //            dump( 'Unprocessed requests: ' . $count );
            $fnc = function () use ($guzzle) {
                while ($r = $this->requests->unprocessed()->first()) {
                    yield $r->send($guzzle);
                }
            };

            $this->promise = ( new EachPromise($fnc(), [ 'concurrency' => $this->threads, ]) )->promise();
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
