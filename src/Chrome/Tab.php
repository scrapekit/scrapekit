<?php

namespace ScrapeKit\ScrapeKit\Chrome;

use Exception;
use ScrapeKit\ScrapeKit\Chrome\Concerns\PausesExecution;
use ScrapeKit\ScrapeKit\Chrome\Exceptions\ElementNotFoundException;
use ScrapeKit\ScrapeKit\Chrome\Utils\Mouse;
use ScrapeKit\ScrapeKit\Common\Utils\Timeout;
use WebSocket\Client;

class Tab
{
    use PausesExecution;

    /**
     * @var Chrome
     */
    protected $chrome;
    protected $params;
    /**
     * @var Client
     */
    protected $client;
    protected $document;
    /**
     * @var WebSocketApi
     */
    protected $api;

    public function api()
    {
        return $this->api;
    }

    public function __construct(Chrome $chrome, $params)
    {

        $this->params = $params;
        $this->chrome = $chrome;

        $this->api = new WebSocketApi(new Client($this->params[ 'webSocketDebuggerUrl' ], [ 'timeout' => 10 ]));
    }

    public function getTitle($refresh = true)
    {
        if ($refresh) {
            $this->params[ 'title' ] = $this->chrome->tabs()->find($this->id())->getTitle(false);
        }

        return $this->params[ 'title' ];
    }

    public function getType()
    {
        return $this->params[ 'type' ];
    }

    public function id()
    {
        return $this->params[ 'id' ];
    }

    public function __get($what)
    {
        //        dd($what);
        return $this->params[ $what ];
    }

    public function close()
    {
        return $this->chrome->api('close/' . $this->params[ 'id' ]);
    }

    public function activate()
    {
        return $this->chrome->api('activate/' . $this->params[ 'id' ]);
    }

    public function userAgent($ua = 'ScrapeKit/0.1')
    {
        $ret = $this->api->send('Emulation.setUserAgentOverride', [ 'userAgent' => $ua ]);

        return $this;
    }

    public function withMouse($callback)
    {
        $callback(new Mouse($this));

        return $this;
    }


    public function domQL($query)
    {
        // TODO integrate DomQL from GCoda
        return $this;
    }

    public function waitUsing($function, $timeout_ms, $interval = 100, $message = 'Timeout')
    {

        $timeout = new Timeout($timeout_ms);

        while (true) {
            dump('iteration');
            try {
                dump('try');
                if ($res = $function()) {
                    return $res;
                }
            } catch (Exception $e) {
                $this->pause($interval);
                $timeout->bump($interval);
                if ($timeout->exceeded()) {
                    throw new TimeoutException($message);
                }
            }
        }
    }

    public function all($selector, $timeout_ms = 10000)
    {

        $this->waitUsing(function () use ($selector) {
            return $this->allOrFail($selector);
        }, $timeout_ms);
    }

    public function find($selector, $timeout_ms = 10000)
    {

        $this->waitUsing(function () use ($selector) {
            return $this->findOrFail($selector);
        }, $timeout_ms);
    }

    public function allOrFail($selector, $rootNodeId = null)
    {
        $res = $this->api->send('DOM.querySelectorAll', [
            'nodeId'   => $rootNodeId ?: $this->document[ 'root' ][ 'nodeId' ],
            'selector' => $selector,
        ]);
        if (count($res[ 'nodeIds' ]) == 0) {
            throw new ElementNotFoundException($selector);
        }

        $nodes = [];
        foreach ($res[ 'nodeIds' ] as $nodeId) {
            $nodes[] = new Node($this, $nodeId);
        }
    }

    public function findOrFail($selector, $rootNodeId = null)
    {
        $res = $this->api->send('DOM.querySelector', [
            'nodeId'   => $rootNodeId ?: $this->document[ 'root' ][ 'nodeId' ],
            'selector' => $selector,
        ]);
        if ($res[ 'nodeId' ] == 0) {
            throw new ElementNotFoundException($selector);
        }

        return new Node($this, $res[ 'nodeId' ]);
    }

    public function geo($lat = null, $lng = null, $accuracy = 0.9)
    {
        if (! $lat) {
            $this->api->send('Emulation.clearGeolocationOverride');

            return $this;
        }

        $this->api->send('Emulation.setGeolocationOverride', [
            'latitude'  => $lat,
            'longitude' => $lng,
            'accuracy'  => $accuracy,
        ]);

        return $this;
    }

    public function navigate($url)
    {
        $this->api->send('Page.enable', [ 'url' => $url ]);
        $this->api->send('Page.navigate', [ 'url' => $url ], 'Page.loadEventFired');
        $this->document = $this->api->send('DOM.getDocument');

        return $this;
    }
}
