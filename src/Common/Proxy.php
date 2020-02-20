<?php

namespace ScrapeKit\ScrapeKit\Common;

class Proxy
{

    protected $params = [
        'protocol' => 'http',
        'host'     => null,
        'port'     => null,
        'username' => null,
        'password' => null,
    ];

    protected $ignoredHosts = [];

    public function ignoredHosts($value = null)
    {
        if ($value !== null) {
            $this->ignoredHosts = $value;

            return $this;
        }

        return $this->ignoredHosts;
    }

    public static function make()
    {
        return new static([]);
    }

    public function host($value = null)
    {
        if ($value !== null) {
            $this->params[ 'host' ] = $value;

            return $this;
        }

        return $this->params[ 'host' ];
    }

    public function port($value = null)
    {
        if ($value !== null) {
            $this->params[ 'port' ] = $value;

            return $this;
        }

        return $this->params[ 'port' ];
    }

    public function protocol($value = null)
    {
        if ($value !== null) {
            $this->params[ 'protocol' ] = $value;

            return $this;
        }

        return $this->params[ 'protocol' ];
    }

    public function username($value = null)
    {
        if ($value !== null) {
            $this->params[ 'username' ] = $value;

            return $this;
        }

        return $this->params[ 'username' ];
    }

    public function password($value = null)
    {
        if ($value !== null) {
            $this->params[ 'password' ] = $value;

            return $this;
        }

        return $this->params[ 'password' ];
    }

    public function auth($value = null)
    {
        if ($value !== null) {
            [ $username, $password ] = explode(':', $value);
            $this->username($username);
            $this->password($password);

            return $this;
        }

        return $this->authToString();
    }


    /**
     * Proxy constructor.
     *
     * @param array $params
     */
    public function __construct(array $params, $ignoredHosts = [])
    {
        $this->params       = array_replace($this->params, $params);
        $this->ignoredHosts = $ignoredHosts;
    }


    public function authToString()
    {
        $ret = '';

        if ($this->params[ 'username' ]) {
            $ret .= $this->params[ 'username' ];
            if ($this->params[ 'password' ]) {
                $ret .= ':' . $this->params[ 'password' ];
            }

            $ret .= '@';
        }

        return $ret;
    }

    public function toString()
    {
        return $this->params[ 'protocol' ]
               . '://'
               . $this->authToString()
               . $this->params[ 'host' ]
               . ':'
               . $this->params[ 'port' ];
    }
}
