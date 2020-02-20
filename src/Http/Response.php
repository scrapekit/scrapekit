<?php

namespace ScrapeKit\ScrapeKit\Http;

use Illuminate\Support\Traits\Macroable;
use ScrapeKit\ScrapeKit\Http\Response\Parser;

use function GuzzleHttp\Psr7\stream_for;

class Response
{
    use Macroable;

    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    public $guzzleResponse;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Headers
     */
    protected $headers;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var mixed
     */
    protected $parser;

    public function __construct($guzzleResponse, Request $request, $parserClass = null)
    {
        $this->guzzleResponse = $guzzleResponse;
        $this->headers();
        $this->request = $request;

        if ($parserClass) {
            $this->parser = new $parserClass($this);
        }
    }

    public function toPsr()
    {
        return $this->guzzleResponse;
    }

    public function parse()
    {

        if ($this->parser instanceof Parser) {
            return $this->parser;
        }

        throw new \Exception('Response parser is not defined');
    }

    public function body($newBody = null)
    {
        if ($newBody !== null) {
            $this->guzzleResponse = $this->toPsr()->withBody(stream_for($newBody));
        }

        if (! $this->body) {
            $this->body = $this->toPsr()->getBody()->getContents();
        }

        return $this->body;
    }

    public function headers()
    {
        $this->headers = new Headers($this->toPsr()->getHeaders());

        return $this->headers;
    }

    public function status()
    {
        return $this->toPsr()->getStatusCode();
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
