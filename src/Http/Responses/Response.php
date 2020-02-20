<?php

namespace ScrapeKit\ScrapeKit\Http\Responses;

use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use ScrapeKit\ScrapeKit\Http\Exceptions\RequestException;
use ScrapeKit\ScrapeKit\Http\Headers;
use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Responses\Parsers\Parser;

use function GuzzleHttp\Psr7\stream_for;

class Response
{
    use Macroable;
    use ForwardsCalls;

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

        $this->parser = $this->getParser($parserClass);
    }

    public function toPsr()
    {
        return $this->guzzleResponse;
    }

    /**
     * @param null $parserClass
     *
     * @return mixed
     * @throws RequestException
     */
    public function parse($parserClass = null)
    {
        // Override parser on the fly
        if ($parserClass !== null) {
            return $this->getParser($parserClass)->data();
        }

        if ($this->parser instanceof Parser) {
            return $this->parser->data();
        }

        if (is_callable($this->parser)) {
            $parser = $this->parser;

            return $parser($this);
        }

        throw new RequestException('Response parser is not defined', $this->request);
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

    public function isXml()
    {
        return $this->contentTypeContains('text/xml');
    }

    public function isJson()
    {
        return $this->contentType('application/json');
    }

    public function header($header)
    {
        $this->headers()->get($header);
    }

    /**
     * @param $parserClass
     *
     * @return null|callable|\ScrapeKit\ScrapeKit\Http\Response\Parsers\Parser
     */
    protected function getParser($parserClass)
    {

        if (is_callable($parserClass)) {
            return $parserClass;
        }

        if (is_string($parserClass)) {
            return new $parserClass($this);
        }

        return null;
    }

    public function dd()
    {
        dd($this->headers()->all(), $this->body());
    }
}
