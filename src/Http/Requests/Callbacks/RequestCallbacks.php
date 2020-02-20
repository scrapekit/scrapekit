<?php

namespace ScrapeKit\ScrapeKit\Http\Requests\Callbacks;

use Exception;
use ScrapeKit\ScrapeKit\Http\Requests\Request;
use Throwable;

class RequestCallbacks
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CallbackCollection[]
     */
    protected $callbacks = [
        'headers_loaded'        => null,
        'body_partially_loaded' => null,
        'body_loaded'           => null,
        'success'               => null,
        'fail'                  => null,
        'last_fail'             => null,
        'timeout'               => null,
    ];

    public const HEADERS_LOADED = 'headers_loaded';
    public const BODY_PARTIALLY_LOADED = 'body_partially_loaded';
    public const BODY_LOADED = 'body_loaded';
    public const SUCCESS = 'success';
    public const FAIL = 'fail';
    public const LAST_FAIL = 'last_fail';
    public const TIMEOUT = 'timeout';

    /**
     * RequestCallbacks constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        foreach ($this->callbacks as $name => $value) {
            $this->callbacks[ $name ] = new CallbackCollection();
        }
    }

    /**
     * @param string $name
     * @param  $handler
     *
     * @return $this
     */
    public function on(string $name, $handler)
    {


        $newHandler = function (...$args) use ($handler) {
            try {
                $handler(...$args);
            } catch (Throwable $e) {
                $this->request->client()->throw($e);
            }
        };


        $this->callbacks[ $name ]->push(new Callback($newHandler));

        return $this;
    }

    public function trigger($name, $data = null)
    {
        return $this->callbacks[ $name ]->fire($this->request, $data);
    }
}
