<?php

namespace ScrapeKit\ScrapeKit\Http\Guzzle\Middleware;

use Closure;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ScrapeKit\ScrapeKit\Http\Request;
use ScrapeKit\ScrapeKit\Http\Request\State;
use ScrapeKit\ScrapeKit\Http\Response;

use function GuzzleHttp\Promise\rejection_for;

class Retry
{

    /**
     * @var callable
     */
    private $nextHandler;
    private $defaultOptions = [
        'scrapekit_request' => null,
    ];

    private $failTriggered = false;

    public static function factory(array $defaultOptions = []): Closure
    {
        return function (callable $handler) use ($defaultOptions) {
            return new static($handler, $defaultOptions);
        };
    }

    public function __construct(callable $nextHandler, array $defaultOptions = [])
    {
        $this->nextHandler    = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     *
     * @return Promise
     */
    public function __invoke(RequestInterface $request, array $options): Promise
    {
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        /** @var callable $next */
        $next = $this->nextHandler;


        return $next($request, $options)
            ->then(
                $this->onFulfilled($request, $options),
                $this->onRejected($request, $options)
            );
    }

    protected function onFulfilled(RequestInterface $guzzleRequest, array $options): callable
    {
        return function (ResponseInterface $response) use ($guzzleRequest, $options) {
            //            dump( 'on fulfilled' );
            if (in_array($response->getStatusCode(), [ '301', '302', '307' ])) {
                return $response;
            }

            /** @var Request $request */
            $request = $options[ 'scrapekit_request' ];
            $request->callbacks()->trigger(Request\RequestCallbacks::BODY_LOADED, $response);

            if (! $request->valid()) {
                $this->failTriggered = true;
                //                dump('rejecting');
                $rejected = $this->onRejected($guzzleRequest, $options);
                $reason   = new RequestException('Invalid response', $guzzleRequest, $response);
                $rejected($reason);

                return rejection_for($reason);
            }

            return $response;
        };
    }

    protected function onRejected(RequestInterface $guzzleRequest, array $options): callable
    {
        return function ($reason) use ($guzzleRequest, $options) {
            /**
             * @var $request Request
             */
            $request = $options[ 'scrapekit_request' ];
            //            dump( $request->id(), $this->failTriggered );
            if (! $this->failTriggered) {
                $request->callbacks()->trigger('fail', $reason);
            }

            $message = $reason->getMessage();
            if (strpos($message, 'Operation timed out') !== false) {
                $request->callbacks()->trigger('timeout', $message);
            }

            if ($request->shouldRetry()) {
                return $this($guzzleRequest, $options);
            }

            return rejection_for($reason);
        };
    }
}
