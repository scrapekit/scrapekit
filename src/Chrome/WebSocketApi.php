<?php

namespace ScrapeKit\ScrapeKit\Chrome;

use WebSocket\Client;

class WebSocketApi
{

    /**
     * @var Client
     */
    public $client;

    public function __construct(Client $client)
    {

        $this->client = $client;
    }

    public function send($method, $params = [], $returnOn = null)
    {

        $this->sendAsync($method, $params);

        while (true) {
            $result = json()->decode($this->client->receive());
            dump('Got response to ' . $method, $result);

            if ($returnOn) {
                if (isset($result[ 'method' ]) && $result[ 'method' ] == $returnOn) {
                    return $result[ 'params' ];
                }
            } elseif (isset($result[ 'result' ])) {
                dump('Returning result');

                return $result[ 'result' ];
            }
        }

        return null;
    }

    public function sendAsync($method, $params = [])
    {
        dump('Method ' . $method);

        $this->client->send(json()->encode([
            'id'     => rand(4, 599),
            'method' => $method,
            'params' => $params,
        ]));

        return $this;
    }
}
