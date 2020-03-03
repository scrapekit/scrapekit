{!! '<'.'?php' !!}


namespace App\ScrapeKit\Http\Requests;


use ScrapeKit\ScrapeKit\Http\Requests\Request;

class {{$name}} extends Request {

    protected $url = '';

    public function __construct( $url ) {
        parent::__construct( $url );
    }

    public function configure() {
        $this
            ->timeouts( [ 8, 11, 15 ] )
            /**//**/
        ;
    }

    public function success() {

        dd( $this->response()->body() );

    }

}
