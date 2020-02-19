<?php

namespace ScrapeKit\ScrapeKit\Http;

use Exception;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use ScrapeKit\ScrapeKit\Http\Request\Callback;
use ScrapeKit\ScrapeKit\Http\Request\RequestCallbacks;
use ScrapeKit\ScrapeKit\Http\Request\RequestOptions;
use ScrapeKit\ScrapeKit\Http\Request\RequestTries;
use ScrapeKit\ScrapeKit\Http\Request\State;
use ScrapeKit\ScrapeKit\Http\Response\Validator;

class Request
{

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $method;

    /**
     * @var RequestCallbacks
     */
    protected $callbacks;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var GuzzleHandler
     */
    protected $handler;

    /**
     * @var Callback
     */
    protected $validator;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var RequestTries
     */
    protected $tries;

    protected $parserClass;


    protected $guzzleOptions = [
        'allow_redirects' => true,
        //        'debug' => true
    ];

    /**
     * Request constructor.
     *
     * @param $method
     * @param $url
     *
     * @throws Exception
     */
    public function __construct(string $method, $url = null, $options = [])
    {

        // Normalize input
        if ($url === null) {
            $url    = $method;
            $method = 'GET';
        } elseif (is_array($url)) {
            $options = $url;
            $url     = $method;
            $method  = 'GET';
        }

        $this->id     = Uuid::uuid4()->toString();
        $this->url    = $url;
        $this->method = $method;

        $this->state     = new State();
        $this->callbacks = new RequestCallbacks($this);
        $this->tries     = new RequestTries();

        $this->validateUsing(function (Request $rq) {
            return $rq->response() && $rq->response()->isOk();
        });

        $this->guzzleOptions [ 'on_headers' ] = function (ResponseInterface $guzzleResponse) {
            $resp = new Response($guzzleResponse);
            $this->setResponse($resp);
            $this->state()->set(State::HEADERS_LOADED);
            $this->callbacks()->trigger('headers_loaded');
        };

        $this->guzzleOptions[ 'scrapekit_request' ] = $this;

        $this->applyOptions($options);
    }


    public function tries()
    {
        return $this->tries;
    }

    /**
     * @return State
     */
    public function state()
    {
        return $this->state;
    }

    public function __validate()
    {
        //        return true;
        $result = $this->validator->fire($this);

        return $result;
    }

    /**
     * @return RequestCallbacks
     */
    public function callbacks()
    {
        return $this->callbacks;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
        $parser         = $this->parserClass;
        if ($parser) {
            $this->response->setParser(new $parser($response));
        }

        return $this;
    }

    public function response()
    {
        return $this->response;
    }

    public function validateUsing(callable $callable)
    {
        $this->validator = new Callback($callable);

        return $this;
    }

    /**
     * @return GuzzleHandler
     */
    public function getHandler(): GuzzleHandler
    {
        return $this->handler;
    }

    /**
     * @param GuzzleHandler $handler
     */
    public function setHandler(GuzzleHandler $handler)
    {
        $this->handler = $handler;

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

    public function guzzleOptions($value = null)
    {
        if ($value !== null) {
            $this->guzzleOptions = $value;
        }

        return $this->guzzleOptions;
    }

    public function url($value = null)
    {
        if ($value !== null) {
            $this->url = $value;
        }

        return $this->url;
    }

    /**
     * @param $options
     */
    protected function applyOptions($options): void
    {

        $options = new RequestOptions($options);

        $additionalGuzzleOptions = $options->get('guzzle', []);
        $this->guzzleOptions     = array_replace($this->guzzleOptions, $additionalGuzzleOptions);

        // Timeout
        $timeout = $options->get('timeout');
        if (is_numeric($timeout)) {
            $this->guzzleOptions[ 'timeout' ] = $timeout;
        } elseif (is_array($timeout)) {
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

        // Register callbacks
        $on = $options->get('on', []);
        foreach ($on as $name => $callbacks) {
            $this->callbacks()->on($name, $callbacks);
        }

        // Validator
        if ($validator = $options->get('validator')) {
            if (is_array($validator)) {
                $validator = Validator::all($validator);
            }
            $this->validateUsing($validator);
        }

        // Parser
        $this->parserClass = $options->get('parser');

        // Misc
        if ($maxTries = $options->get('max_tries', null)) {
            $this->tries()->max($maxTries);
        }
    }

    public static function wrap($input)
    {
        if ($input instanceof static) {
            return $input;
        }

        return new static(...$input);
    }
}
