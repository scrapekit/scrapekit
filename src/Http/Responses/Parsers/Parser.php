<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers;

use ScrapeKit\ScrapeKit\Http\Responses\Response\Response;

abstract class Parser
{

    /**
     * @var Response
     */
    protected $response;

    public function __construct(Response $response)
    {

        $this->response = $response;
    }

    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->response->{$property};
    }

    public function __call($method, $args)
    {
        if (method_exists($this->response, $method)) {
            return call_user_func([ $this->response, $method ], $args);
        }
    }

    public static function autodetect()
    {
        return function (Response $response) {
            if ($response->isJson()) {
                return ( new JsonParser($response) )->data();
            }
            if ($response->isXml()) {
                return ( new XmlParser($response) )->data();
            }
            if ($response->isHtml()) {
                return ( new HtmlParser($response) )->data();
            }

            return ( new DummyParser($response) )->data();
        };
    }

    public static function json()
    {
        return JsonParser::class;
    }

    public static function xml()
    {
        return XmlParser::class;
    }

    public static function html()
    {
        return HtmlParser::class;
    }

    public static function regex()
    {
        return RegexParser::class;
    }
}
