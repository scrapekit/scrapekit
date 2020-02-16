<?php

namespace ScrapeKit\ScrapeKit\Chrome;


class Chrome {

    public $url;

    public function __construct( $url ) {
        $this->url = $url;
    }

    public function api( $method ) {

        $curl_handle = curl_init( $this->url . '/json/' . $method );
        curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT, 2 );
        curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl_handle, CURLOPT_USERAGENT, 'Your application name' );
        $data = curl_exec( $curl_handle );
        curl_close( $curl_handle );

        return json()->decode( $data );
    }

    public function tabs() {
        return new Tabs( $this, $this->api( 'list' ) );
    }


}
