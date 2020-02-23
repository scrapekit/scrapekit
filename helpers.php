<?php

use ScrapeKit\ScrapeKit\Common\Utils\BlackBox;
use ScrapeKit\ScrapeKit\Common\Utils\Json;
use ScrapeKit\ScrapeKit\ScrapeKit;

function json() {
    return new Json();
}

function scrapekit() {
    return new ScrapeKit;
}

function blackbox( callable $callback ) {
    return BlackBox::try( $callback );
}

if ( ! function_exists( 'fix_url' ) ) {

    function fix_url( $url ) {
        if ( strpos( $url, 'http://' ) === 0 ) {
            return $url;
        }
        if ( strpos( $url, 'https://' ) === 0 ) {
            return $url;
        }

        return 'http://' . $url;
    }
}
