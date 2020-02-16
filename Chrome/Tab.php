<?php


namespace ScrapeKit\ScrapeKit\Chrome;


use Exception;
use ScrapeKit\ScrapeKit\Chrome\Utils\Mouse;
use WebSocket\Client;

class Tab {

    /**
     * @var Chrome
     */
    protected $chrome;
    protected $params;
    /**
     * @var Client
     */
    protected $client;
    private $document;

    public function __construct( Chrome $chrome, $params ) {

        $this->params = $params;
        $this->chrome = $chrome;
    }

    public function __get( $what ) {
        //        dd($what);
        return $this->params[ $what ];
    }

    protected function connect() {
        $this->client = new Client( $this->params[ 'webSocketDebuggerUrl' ] );
        $this->client->setTimeout( 10 );
    }

    public function close() {
        return $this->chrome->api( 'close/' . $this->params[ 'id' ] );
    }

    public function activate() {
        return $this->chrome->api( 'activate/' . $this->params[ 'id' ] );
    }

    public function userAgent( $ua = 'ScrapeKit/0.1' ) {
        $ret = $this->send( 'Emulation.setUserAgentOverride', [ 'userAgent' => $ua ] );

        return $this;
    }

    public function withMouse( $callback ) {
        $callback( new Mouse( $this ) );

        return $this;
    }

    public function sendAsync( $method, $params = [] ) {
        dump( 'Method ' . $method );
        if ( ! $this->client ) {
            $this->connect();
        }

        $this->client->send( json()->encode( [
            'id'     => rand( 4, 599 ),
            'method' => $method,
            'params' => $params,
        ] ) );

        return $this;
    }

    public function send( $method, $params = [], $returnOn = null ) {

        $this->sendAsync( $method, $params );

        while ( true ) {
            $res = $this->client->receive();
            $d   = json()->decode( $res );
            dump( 'Got response to ' . $method, $d );

            if ( $returnOn ) {
                if ( isset( $d[ 'method' ] ) && $d[ 'method' ] == $returnOn ) {
                    return $d[ 'params' ];
                }
            } elseif ( isset( $d[ 'result' ] ) ) {
                dump( 'Returning result' );

                return $d[ 'result' ];
            }
        }
        //        return $d[ 'result' ];
        //        return isset( $d[ 'result' ] ) ? $d[ 'result' ] : $d;
    }

    public function domQL( $query ) {
        // TODO integrate DomQL from GCoda
        return $this;
    }

    public function all( $selector, $timeout_ms = 10000 ) {

        $waited = 0;

        while ( true ) {
            dump( 'iteration' );
            try {
                dump( 'try' );
                $res = $this->allOrFail( $selector );

                return $res;
            }
            catch ( Exception $e ) {
                $waited += 100;
                $this->pause( 100 );
                if ( $waited > $timeout_ms ) {
                    throw new Exception( 'Timeout!' );
                }
            }


        }


    }

    public function geo( $lat = null, $lng = null ) {
        if ( ! $lat ) {
            $this->send( 'Emulation.clearGeolocationOverride' );

            return $this;
        }


        $this->send( 'Emulation.setGeolocationOverride', [
            'latitude'  => $lat,
            'longitude' => $lng,
            'accuracy'  => 0.9,
        ] );

        return $this;
    }

    public function find( $selector ) {

        while ( true ) {
            dump( 'iteration' );
            try {
                $res = $this->findOrFail( $selector );

                return $res;
            }
            catch ( Exception $e ) {
                $this->pause( 100 );
            }

        }


    }

    public function pause( $ms ) {
        usleep( $ms * 1000 );

        return $this;
    }

    public function allOrFail( $selector ) {
        $res = $this->send( 'DOM.querySelectorAll', [ 'nodeId' => 1, 'selector' => $selector ] );
        if ( count( $res[ 'nodeIds' ] ) == 0 ) {
            throw new Exception( 'Elements not found' );
        }

        return $res[ 'nodeIds' ];
    }

    public function findOrFail( $selector ) {
        $res = $this->send( 'DOM.querySelector', [
            'nodeId'   => $this->document[ 'root' ][ 'nodeId' ],
            'selector' => $selector,
        ] );
        if ( $res[ 'nodeId' ] == 0 ) {
            throw new Exception( 'Element not found' );
        }

        return new Node( $this, $res[ 'nodeId' ] );
    }

    public function navigate( $url ) {
        $this->send( 'Page.enable', [ 'url' => $url ] );
        $this->send( 'Page.navigate', [ 'url' => $url ], 'Page.loadEventFired' );
        $this->document = $this->send( 'DOM.getDocument' );

        return $this;
    }

}
