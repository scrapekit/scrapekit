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
