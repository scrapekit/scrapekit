<?php

namespace ScrapeKit\ScrapeKit\Common\Utils;

use Exception;

class BlackBox
{

    /**
     * @var callable
     */
    protected $callback;
    /**
     * @var callable[]
     */
    protected $fail_callbacks = [];
    /**
     * @var callable[]
     */
    protected $success_callbacks = [];
    /**
     * @var bool
     */
    protected $failed = false;
    /**
     * @var Exception
     */
    protected $exception;
    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var bool
     */
    protected $executed = false;


    /**
     * BlackBox constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {

        $this->callback = $callback;
    }

    /**
     * @return $this
     */
    public function run()
    {
        if (! $this->executed) {
            try {
                $cb           = $this->callback;
                $this->result = $cb();
                $this->failed = false;

                foreach ($this->success_callbacks as $cb) {
                    $cb($this->result);
                }
            } catch (Exception $e) {
                $this->failed    = true;
                $this->exception = $e;

                foreach ($this->fail_callbacks as $cb) {
                    $cb($this->exception);
                }
            }
        }

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return static
     */
    public static function try(callable $callback)
    {
        return new static($callback);
    }

    /**
     * @param callable $callback
     *
     * @return BlackBox
     */
    public function onSuccess(callable $callback)
    {
        $this->success_callbacks[] = $callback;

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return static
     */
    public function onFail(callable $callback)
    {
        $this->fail_callbacks[] = $callback;

        return $this;
    }

    /**
     * @return mixed
     */
    public function result()
    {
        return $this->run()->result;
    }

    /**
     * @return Exception
     */
    public function exception()
    {
        return $this->run()->exception;
    }

    /**
     * @return bool
     */
    public function passes()
    {
        return ! $this->fails();
    }

    /**
     * @return bool
     */
    public function fails()
    {
        return $this->run()->failed;
    }
}
