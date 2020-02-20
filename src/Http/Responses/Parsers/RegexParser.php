<?php

namespace ScrapeKit\ScrapeKit\Http\Responses\Parsers;

class RegexParser extends Parser
{


    public function all($regex)
    {

        if (preg_match_all($regex, $this->response->body(), $matches)) {
            return $matches[ 1 ];
        }

        return [];
    }

    public function first($regex)
    {

        if (preg_match($regex, $this->response->body(), $matches)) {
            return $matches[ 1 ];
        }

        return null;
    }
}
