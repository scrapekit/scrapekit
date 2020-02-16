<?php

namespace ScrapeKit\ScrapeKit\Utils;

class Json {

    public function encode( $data ) {
        return json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    public function decode( $data ) {
        return json_decode( $data, true );
    }
}
