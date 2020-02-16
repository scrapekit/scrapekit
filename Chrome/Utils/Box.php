<?php


namespace ScrapeKit\ScrapeKit\Chrome\Utils;


class Box {

    /**
     * @var Point
     */
    public $topLeft;
    /**
     * @var Point
     */
    public $topRight;
    /**
     * @var Point
     */
    public $bottomRight;
    /**
     * @var Point
     */
    public $bottomLeft;

    public function __construct( Point $topLeft, Point $topRight, Point $bottomRight, Point $bottomLeft ) {
        $this->topLeft     = $topLeft;
        $this->topRight    = $topRight;
        $this->bottomRight = $bottomRight;
        $this->bottomLeft  = $bottomLeft;
    }

    public static function fromFlatArray( $array ) {

        return new static(
            new Point( $array[ 0 ], $array[ 1 ] ),
            new Point( $array[ 2 ], $array[ 3 ] ),
            new Point( $array[ 4 ], $array[ 5 ] ),
            new Point( $array[ 6 ], $array[ 7 ] )
        );

    }

    public function width() {
        return $this->topRight->x - $this->topLeft->x;
    }

    public function height() {
        return $this->bottomLeft->y - $this->topLeft->y;
    }

    public function center() {

        return new Point(
            ceil( $this->topLeft->x + $this->width() / 2 ),
            ceil( $this->topLeft->y + $this->height() / 2 )
        );

    }

}
