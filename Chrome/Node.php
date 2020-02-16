<?php


namespace ScrapeKit\ScrapeKit\Chrome;


use Illuminate\Support\Traits\ForwardsCalls;
use ScrapeKit\ScrapeKit\Chrome\Utils\Box;
use ScrapeKit\ScrapeKit\Chrome\Utils\Mouse;

class Node {
    /**
     * @var int
     */
    public $id;
    /**
     * @var Tab
     */
    protected $tab;

    protected $objectId;

    public function __construct( Tab $tab, int $id ) {
        $this->id  = $id;
        $this->tab = $tab;
    }

    public function tab() {
        return $this->tab;
    }

    public function objectId() {
        if ( ! $this->objectId ) {
            $d              = $this->tab->send( 'DOM.resolveNode', [ 'nodeId' => $this->id ] );
            $this->objectId = $d[ 'object' ][ 'objectId' ];
        }

        return $this->objectId;
    }

    public function callFunction( $function ) {

        return $this->tab->send( 'Runtime.callFunctionOn', [
            'functionDeclaration' => $function,
            'objectId'            => $this->objectId(),
        ] );
    }

    public function text() {

        $d = $this->callFunction( 'function() { return this.innerText; }' );

        return $d[ 'result' ][ 'value' ];
    }

    public function click() {

        $this->tab->withMouse( function ( Mouse $mouse ) {
            $point = $this->getBox()->center();
            $mouse->atPoint( $point )->click( 'left' );


        } );

        return $this;

    }

    /**
     * @return Box
     */
    protected function getBox(): Box {
        $bm = $this->tab->send( 'DOM.getBoxModel', [ 'nodeId' => $this->id ] );

        return Box::fromFlatArray( $bm[ 'model' ][ 'content' ] );
    }
}
