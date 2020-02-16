<?php


namespace ScrapeKit\ScrapeKit\Chrome\Utils;


class Point {

    /**
     * @var int
     */
    public $x;
    /**
     * @var int
     */
    public $y;

    public function __construct( int $x, int $y ) {
        $this->x = $x;
        $this->y = $y;
    }
}
