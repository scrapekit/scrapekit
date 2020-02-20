<?php

namespace ScrapeKit\ScrapeKit\Http;

use Illuminate\Support\Traits\Macroable;

use function GuzzleHttp\Psr7\stream_for;

class Response
{
    use Macroable;

    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    public $guzzleResponse;
    protected $headers;

    protected $body;

    protected $parser;

    public function __construct($guzzleResponse)
    {
        $this->guzzleResponse = $guzzleResponse;
        $this->headers();
    }

    public function parse()
    {
        return $this->parser;
    }

    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    public function body($newBody = null)
    {

        if ($newBody !== null) {
            $this->guzzleResponse = $this->guzzleResponse->withBody(stream_for($newBody));
        }

        if (! $this->body) {
            $this->body = $this->guzzleResponse->getBody()->getContents();
        }

        return $this->body;
    }

    public function headers()
    {
        $this->headers = new Headers($this->guzzleResponse->getHeaders());

        return $this->headers;
    }

    public function status()
    {
        return $this->guzzleResponse->getStatusCode();
    }

    public function isOk()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    public function contentTypeContains($what)
    {
        $contentType = $this->headers()->first('Content-Type');

        return strpos($contentType, $what) !== false;
    }

    public function contentType($matchAgainst = null)
    {

        $contentType = $this->headers()->first('Content-Type');

        if ($matchAgainst) {
            return $contentType == $matchAgainst;
        }

        return $contentType;
    }

    public function isHtml()
    {
        return $this->contentTypeContains('text/html');
    }

    public function isJson()
    {
        return $this->contentType('application/json');
    }

    public function header($header)
    {
        $this->headers()->get($header);
    }
}
