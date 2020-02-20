<?php

namespace ScrapeKit\ScrapeKit\Common;

class LuminatiProxy implements MakesProxy
{

    protected $params = [
        'zone'     => 'static',
        'session'  => null,
        'username' => null,
        'password' => null,
    ];

    public function __construct($params = [])
    {
        $this->params = array_replace($this->params, $params);
    }

    public static function make()
    {
        return new static();
    }

    public function randomSession()
    {
        $this->params[ 'session' ] = mt_rand();

        return $this;
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

    public function session($value = null)
    {
        if ($value !== null) {
            $this->params[ 'session' ] = $value;

            return $this;
        }

        return $this->params[ 'session' ];
    }

    public function zone($value = null)
    {
        if ($value !== null) {
            $this->params[ 'zone' ] = $value;

            return $this;
        }

        return $this->params[ 'zone' ];
    }


    public function auth($value)
    {
        if ($value !== null) {
            [ $username, $password ] = explode(':', $value);
            $this->username($username);
            $this->password($password);

            return $this;
        }
    }

    public function toProxy(): Proxy
    {
        return Proxy::make()
                    ->host('zproxy.lum-superproxy.io')
                    ->port('22225')
                    ->username('lum-customer-' . $this->username() . ( $this->session() ? ( '-session-' . $this->session() ) : '' ) . '-zone-' . $this->zone())
                    ->password($this->password());
    }
}
