<?php

namespace ScrapeKit\ScrapeKit\Http;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use ScrapeKit\ScrapeKit\Http\Exceptions\InvalidResponseException;
use ScrapeKit\ScrapeKit\Http\Request\Callback;
use ScrapeKit\ScrapeKit\Http\Request\Plugin;
use ScrapeKit\ScrapeKit\Http\Request\RequestCallbacks;
use ScrapeKit\ScrapeKit\Http\Request\RequestTries;
use ScrapeKit\ScrapeKit\Http\Request\State;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ProvidesValidation;
use ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns\ResponseValidationInterface;
use ScrapeKit\ScrapeKit\Http\Response\Validator;

use function GuzzleHttp\Promise\rejection_for;

/**
 * Class Request
 * @package ScrapeKit\ScrapeKit\Http
 */
class Request
{
    use Macroable;

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
     * @var Promise
     */
    protected $promise;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var array
     */
    protected $guzzleOptions = [];

    protected $plugins = [];

    /**
     * @var string
     */
    protected $parserClass;

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
        $this->state     = new State();

        $this->validator(Validator::isOk());

        $this->guzzleOptions [ 'on_headers' ] = function (ResponseInterface $guzzleResponse) {
            $this->callbacks()->trigger(RequestCallbacks::HEADERS_LOADED, $guzzleResponse);
        };

        $this->guzzleOptions[ 'scrapekit_request' ] = $this;

        $this->registerCallbacks();
        $this->configure();

        foreach ($this->plugins as $plugin) {
            $plugin->configure($this);
        }

        if (method_exists($this, 'success')) {
            $this->onSuccess([ $this, 'success' ]);
        }
    }

    public function withPlugins($plugins)
    {
        $plugins = Arr::wrap($plugins);
        foreach ($plugins as $plugin) {
            $this->withPlugin($plugin);
        }

        return $this;
    }

    public function parseResponseWith($parserClass)
    {
        $this->parserClass = $parserClass;

        return $this;
    }

    public function withPlugin($plugin)
    {

        if (! $plugin instanceof Plugin) {
            $pluginClass = $plugin;
            $plugin      = new $plugin($this);
        } else {
            $pluginClass = get_class($plugin);
        }

        $meth = get_class_methods($pluginClass);

        foreach ($meth as $m) {
            if (Str::startsWith($m, 'macro')) {
                $newName = Str::after($m, 'macro');
                $newName = Str::camel($newName);
            } elseif (Str::contains(( new \ReflectionMethod($pluginClass, $m) )->getDocComment(), '@macro')) {
                $newName = $m;
            } else {
                continue;
            }


            static::macro($newName, [ $plugin, $m ]);
        }

        $this->plugins[ get_class($plugin) ] = $plugin;

        return $this;
    }

    public function state()
    {
        return $this->state;
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

        $onRejected = function ($reason, $trigger = true) use ($guzzle) {
            if ($trigger) {
                $this->callbacks()->trigger('fail', $reason);
            }

            $message = $reason->getMessage();
            if (strpos($message, 'Operation timed out') !== false) {
                $this->callbacks()->trigger('timeout', $message);
            }

            if ($this->shouldRetry()) {
                return $this->send($guzzle);
            }

            return rejection_for($reason);
        };

        $this->promise = $guzzle->sendAsync(new GuzzleRequest($this->method(), $this->url()), $this->guzzleOptions)
                                ->then(function ($response) use ($onRejected) {
                                    $this->callbacks()->trigger(Request\RequestCallbacks::BODY_LOADED, $response);

                                    if (! $this->valid()) {
                                        $reason = new InvalidResponseException('Invalid response');
                                        $onRejected($reason, false);

                                        return rejection_for($reason);
                                    }
                                }, $onRejected);

        $this->promise->otherwise(function ($e) {
            if ($e instanceof InvalidResponseException) {
                return;
            }
            if ($e instanceof RequestException) {
                return;
            }
        });


        $this->state()->set(State::PROCESSING);

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

        if ($this->response() && $this->parserClass && $this->response()->parse() instanceof ResponseValidationInterface) {
            return $this->response()->parse()->validate();
        }

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
                $this->response(new Response($response, $this, $this->parserClass));
                if ($this->valid()) {
                    dump($this->url() . ' ' . 'valid');
                    $this->callbacks()->trigger(RequestCallbacks::SUCCESS);
                } else {
                    dump($this->url() . ' ' . 'invalid');
                    $this->callbacks()->trigger(RequestCallbacks::FAIL, new Exception('Invalid body'));
                }
            })
            ->onPartialLoad(function (Request $request, $message) {
                dump($this->url() . ' ' . 'Partial Load - ' . $message);
            })
            ->onTimeout(function (Request $request, $message) {
                //                                dump( $this->url() . ' ' . 'Timeout - ' . $message );
            })
            ->onSuccess(function (Request $request) {
                dump($this->url() . ' ' . 'SUCCESS');
                $this->state()->set(State::FINISHED);
            })
            ->onFail(function (Request $request, $reason) {
                $this->tries()->increment();
                dump($this->url() . ' ' . 'fail triggered - ' . $reason->getMessage());

                if ($this->tries()->exceeded()) {
                    $this->callbacks()->trigger(RequestCallbacks::LAST_FAIL, $reason);
                }
            })
            ->onLastFail(function (Request $request, $reason) {
                //                dump( $this->url() . ' ' . 'last fail triggered', $this->url() );
                $this->state()->set(State::FINISHED);
            })
            ->onHeaders(function (Request $request, $guzzleResponse) {
                //                dd(( new Response($guzzleResponse) )->body());
                //                dump( $this->url() . ' ' . 'headers loaded' );
            })
            /**/
            /**/
        ;
    }
}
