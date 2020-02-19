<?php

namespace ScrapeKit\ScrapeKit\Http;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use ScrapeKit\ScrapeKit\Http\Guzzle\Middleware\Retry;
use ScrapeKit\ScrapeKit\Http\Request as ScrapeKitRequest;
use ScrapeKit\ScrapeKit\Http\Request\RequestCollection;
use ScrapeKit\ScrapeKit\Http\Request\State;

use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;

class GuzzleHandler
{

    /**
     * @var RequestCollection
     */
    public $requests;
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;
    /**
     * @var array
     */
    private $promises;

    /**
     * GuzzleHandler constructor.
     *
     * @param $requests
     */
    public function __construct(RequestCollection $requests)
    {
        $this->requests = $requests;
    }

    public function start($options = [])
    {

        $this->promises = [];

        $stack = new HandlerStack();
        $stack->setHandler(new CurlMultiHandler());
        $stack->push(\GuzzleHttp\Middleware::redirect(), 'allow_redirects');
        $stack->push(\GuzzleHttp\Middleware::cookies(), 'cookies');
        $stack->push(\GuzzleHttp\Middleware::prepareBody(), 'prepare_body');
        $stack->push(\GuzzleHttp\Middleware::httpErrors(), 'http_errors');
        $stack->push(\ScrapeKit\ScrapeKit\Http\Guzzle\Middleware\Retry::factory());

        $options[ 'handler' ] = $stack;

        $this->client = new \GuzzleHttp\Client($options);

        /** @var ScrapeKitRequest $request */
        foreach ($this->requests as $request) {
            $request->setHandler($this);
            $promise                           = $this->client->sendAsync(new Request($request->method(), $request->url()), $request->guzzleOptions());
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

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): \GuzzleHttp\Client
    {
        return $this->client;
    }
}
