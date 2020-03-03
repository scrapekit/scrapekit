<?php

namespace ScrapeKit\ScrapeKit\Console;

use Afterflow\Recipe\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRequest extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrapekit:request {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new request';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $rqdir = app_path( 'ScrapeKit/Http/Requests' );
        File::ensureDirectoryExists( $rqdir );

        $rq = Recipe::make( [
            'name' => $this->argument( 'name' ),
        ] )->template( __DIR__ . '/../../stubs/Request.blade.php' )->render();

        file_put_contents( $rqdir . '/' . $this->argument( 'name' ) . '.php', $rq );
        $this->info( 'Request generated!' );
    }
}
