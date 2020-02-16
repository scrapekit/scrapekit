<?php


namespace ScrapeKit\ScrapeKit\Chrome;


use Illuminate\Support\Traits\ForwardsCalls;

class Tabs {
    use ForwardsCalls;

    public $chrome;
    protected $tabs = [];

    public function __construct( $chrome, $tabs ) {

        $this->chrome = $chrome;
        $this->tabs   = collect( $tabs )->map( function ( $tab ) {
            if ( ! $tab instanceof Tab ) {
                return new Tab( $this->chrome, $tab );
            }

            return $tab;
        } );

    }

    public function new( $url = null ) {

        $act = 'new';
        if ( $url ) {
            $act .= '?url=' . $url;
        }

        return new Tab( $this->chrome, $this->chrome->api( $act ) );
    }

    public function __call( $meth, $params ) {
        return $this->forwardCallTo( $this->tabs, $meth, $params );
    }

    public function all() {
        return $this->tabs;
    }

}
