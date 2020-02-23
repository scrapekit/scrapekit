<?php

namespace ScrapeKit\ScrapeKit\Tests;

use PHPUnit\Framework\TestCase;
use ScrapeKit\ScrapeKit\Http\Requests\Request;

class HttpTest extends TestCase {

    public function testCache() {

        $c = scrapekit()->http();
        $r = Request::make( 'http://httpbin.org/delay/2' )->cache('/tmp');

        $rr = $c->request( $r );


        $this->assertTrue( true );
    }
}
