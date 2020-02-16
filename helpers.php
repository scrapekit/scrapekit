<?php

use ScrapeKit\ScrapeKit\Chrome\Chrome;
use ScrapeKit\ScrapeKit\Utils\Json;

function chrome( $url = 'http://localhost:9222' ) {
    return new Chrome( $url );
}

function json() {
    return new Json();
}
