<?php


namespace ScrapeKit\ScrapeKit\Chrome\Utils;


use ScrapeKit\ScrapeKit\Chrome\Tab;

class Mouse {


    /**
     * @var Tab
     */
    public $tab;
    /**
     * @var Point
     */
    protected $point;

    public function __construct( Tab $tab ) {
        $this->tab = $tab;
    }

    public function atPoint( Point $point ) {
        $this->point = $point;

        return $this;
    }

    public function click( $buttonName ) {
        $this->pressButton( $buttonName );
        $this->releaseButton( $buttonName );

        return $this;
    }

    public function doubleClick( $buttonName ) {

        $this->pressButton( $buttonName, 2 );
        $this->releaseButton( $buttonName, 2 );

        return $this;
    }

    public function releaseButton( $buttonName = 'left', $clickCount = 1 ) {

        $this->tab->send( 'Input.dispatchMouseEvent', [
            'type'       => 'mouseReleased',
            'x'          => $this->point->x,
            'y'          => $this->point->y,
            'button'     => $buttonName,
            'clickCount' => $clickCount,
        ] );
    }

    public function pressButton( $buttonName = 'left', $clickCount = 1 ) {

        $this->tab->send( 'Input.dispatchMouseEvent', [
            'type'       => 'mousePressed',
            'x'          => $this->point->x,
            'y'          => $this->point->y,
            'button'     => $buttonName,
            'clickCount' => $clickCount,
        ] );
    }

}
