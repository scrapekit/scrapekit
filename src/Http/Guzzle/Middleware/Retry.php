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

            $request = $options[ 'scrapekit_request' ];

            $request->setResponse(new Response($response));

            //            dump( 'on fulfilled 2' );
            if ($valid = $request->__validate()) {
                $request->state()->set(State::SUCCESS);
                $request->callbacks()->trigger('success');
//                dump( $request->response()->body() );
                dump('valid');

                $request->tries()->increment();

                return $response;
            } else {
                dump('invalid');
                //                dd( 'invalid' );
                $rejected = $this->onRejected($guzzleRequest, $options);
                $reason   = new RequestException('Invalid response', $guzzleRequest, $response);
                $rejected($reason);

                //                dump( 'after rejected' );

                return rejection_for($reason);
            }

            //            dd( 1 );


            //            dd( $this->request->id(), $request, $options );

            //            return $this->shouldRetryHttpResponse( $options, $response )
            //                ? $this->doRetry( $request, $options, $response )
            //                : $this->returnResponse( $options, $response );
        };
    }

    protected function onRejected(RequestInterface $guzzleRequest, array $options): callable
    {
        return function ($reason) use ($guzzleRequest, $options) {
            /**
             * @var $request Request
             */
            $request = $options[ 'scrapekit_request' ];
            $request->tries()->increment();
            dump($request->id());
            $request->state()->set(State::FAIL);
            $request->callbacks()->trigger('fail');

            $message = $reason->getMessage();
            if (strpos($message, 'Operation timed out') !== false) {
                $request->callbacks()->trigger('timeout', $message);
            }


            if ($request->tries()->exceeded()) {
                //                dump( 'tries exceeded' );
                $request->state()->set(State::LAST_FAIL);
                $request->callbacks()->trigger('last_fail');
            } else {
                //                dump( 'tries not exceeded' );

                // restart the request
                return $this($guzzleRequest, $options);
            }


            //            dd( '2' );
            //            // If was bad response exception, test if we retry based on the response headers
            //            if ($reason instanceof BadResponseException) {
            //                if ($this->shouldRetryHttpResponse($options, $reason->getResponse())) {
            //                    return $this->doRetry($request, $options, $reason->getResponse());
            //                }
            //                // If this was a connection exception, test to see if we should retry based on connect timeout rules
            //            } elseif ($reason instanceof ConnectException) {
            //                // If was another type of exception, test if we should retry based on timeout rules
            //                if ($this->shouldRetryConnectException($options)) {
            //                    return $this->doRetry($request, $options);
            //                }
            //            }
            //
            //            // If made it here, then we have decided not to retry the request
            return rejection_for($reason);
        };
    }
}
