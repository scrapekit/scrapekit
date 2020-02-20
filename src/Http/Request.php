<?php

namespace ScrapeKit\ScrapeKit\Http;

use Exception;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use ScrapeKit\ScrapeKit\Http\Request\Callback;
use ScrapeKit\ScrapeKit\Http\Request\RequestCallbacks;
use ScrapeKit\ScrapeKit\Http\Request\RequestTries;
use ScrapeKit\ScrapeKit\Http\Response\Validator;

/**
 * Class Request
 * @package ScrapeKit\ScrapeKit\Http
 */
class Request
{

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $url = '';
    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var RequestCallbacks
     */
    protected $callbacks;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Callback
     */
    protected $validator;

    /**
     * @var RequestTries
     */
    protected $tries;

    /**
     * @var
     */
    protected $promise;

    //    protected $parserClass;


    /**
     * @var array
     */
    protected $guzzleOptions = [];

    /**
     * Request constructor.
     *
     * @param $url
     *
     * @throws Exception
     */
    public function __construct($url = null)
    {

        $this->id = Uuid::uuid4()->toString();

        if ($url) {
            $this->url = $url;
        }

        $this->callbacks = new RequestCallbacks($this);
        $this->tries     = new RequestTries();

        $this->validator(Validator::isOk());

        $this->guzzleOptions [ 'on_headers' ] = function (ResponseInterface $guzzleResponse) {
            $this->callbacks()->trigger(RequestCallbacks::HEADERS_LOADED, $guzzleResponse);
        };

        $this->guzzleOptions[ 'scrapekit_request' ] = $this;


        $this->registerCallbacks();

        $this->configure();
    }

    public function configure()
    {
        return $this;
    }

    public function tries($max = null)
    {
        if ($max) {
            $this->tries->max($max);

            return $this;
        }

        return $this->tries;
    }

    public function send(\GuzzleHttp\Client $guzzle)
    {
        $this->promise = $guzzle->sendAsync(new GuzzleRequest($this->method(), $this->url()), $this->guzzleOptions);

        $this->promise->then();


        return $this->promise;
    }

    /**
     * @return RequestCallbacks
     */
    public function callbacks()
    {
        return $this->callbacks;
    }

    public function response(Response $response = null)
    {
        if ($response) {
            $this->response = $response;
        }

        return $this->response;
    }

    public function validator(callable $callable = null)
    {
        if ($callable === null) {
            return $this->validator;
        }

        $this->validator = new Callback($callable);

        return $this;
    }

    public function id($value = null)
    {
        if ($value !== null) {
            $this->id = $value;
        }

        return $this->id;
    }

    public function method($value = null)
    {
        if ($value !== null) {
            $this->method = $value;
        }

        return $this->method;
    }

    public function client($value = null)
    {
        if ($value !== null) {
            $this->client = $value;
        }

        return $this->client;
    }

    public function url($value = null)
    {
        if ($value !== null) {
            $this->url = $value;
        }

        return $this->url;
    }

    public function timeouts($timeout = null)
    {
        if ($timeout === null) {
            return $this->guzzleOptions[ 'timeout' ];
        }

        if (is_numeric($timeout)) {
            $timeout = [ 'load' => $timeout ];
        }

        if (is_array($timeout)) {
            $timeoutC = Arr::get($timeout, 0, Arr::get($timeout, 'connect', null));
            $timeoutL = Arr::get($timeout, 1, Arr::get($timeout, 'load', null));
            $timeoutR = Arr::get($timeout, 2, Arr::get($timeout, 'read', null));

            if ($timeoutC !== null) {
                $this->guzzleOptions[ 'connect_timeout' ] = $timeoutC;
            }
            if ($timeoutL !== null) {
                $this->guzzleOptions[ 'timeout' ] = $timeoutL;
            }
            if ($timeoutR !== null) {
                $this->guzzleOptions[ 'read_timeout' ] = $timeoutR;
            }
        }

        return $this;
    }

    /**
     * @param $url
     *
     * @return static
     * @throws Exception
     */
    public static function make($url)
    {
        if ($url instanceof static) {
            return $url;
        }

        return new static($url);
    }

    public function shouldRetry()
    {
        return ! $this->tries()->exceeded() && ! $this->valid();
    }

    public function valid()
    {
        return $this->response() && $this->validator()->fire($this);
    }

    public function onPartialLoad(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::BODY_PARTIALLY_LOADED, $callback);

        return $this;
    }

    public function onLastFail(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::LAST_FAIL, $callback);

        return $this;
    }

    public function onFail(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::FAIL, $callback);

        return $this;
    }

    public function onSuccess(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::SUCCESS, $callback);

        return $this;
    }

    public function onLoad(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::BODY_LOADED, $callback);

        return $this;
    }

    public function onTimeout(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::TIMEOUT, $callback);

        return $this;
    }

    public function onHeaders(callable $callback)
    {
        $this->callbacks()->on(RequestCallbacks::HEADERS_LOADED, $callback);

        return $this;
    }

    protected function registerCallbacks(): void
    {
        $this
            ->onLoad(function (Request $request, ResponseInterface $response) {
                $this->response(new Response($response));
                if ($this->valid()) {
                    dump('valid');
                    $this->callbacks()->trigger(RequestCallbacks::SUCCESS);
                } else {
                    dump('invalid');
                    $this->callbacks()->trigger(RequestCallbacks::FAIL, new Exception('Invalid body'));
                }
            })
            ->onPartialLoad(function (Request $request, $message) {
                dump('Partial Load - ' . $message);
            })
            ->onTimeout(function (Request $request, $message) {
                dump('Timeout - ' . $message);
            })
            ->onSuccess(function (Request $request) {
                dump('SUCCESS');
            })
            ->onFail(function (Request $request, $reason) {
                $this->tries()->increment();
                dump('fail triggered - ' . $reason->getMessage());

                if ($this->tries()->exceeded()) {
                    $this->callbacks()->trigger(RequestCallbacks::LAST_FAIL, $reason);
                }
            })
            ->onLastFail(function (Request $request, $reason) {
                dump('last fail triggered', $this->url());
                dd($request->response->body());
            })
            ->onHeaders(function (Request $request, $guzzleResponse) {
                dump('headers loaded');
            })
            /**/
            /**/
        ;
    }
}
