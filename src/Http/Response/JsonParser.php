<?php

namespace ScrapeKit\ScrapeKit\Http\Response;

class JsonParser extends Parser
{

    public function data()
    {
        return json()->decode($this->response->body());
    }
}
