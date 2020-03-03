<?php


namespace ScrapeKit\ScrapeKit;


use Illuminate\Support\ServiceProvider;
use ScrapeKit\ScrapeKit\Console\MakeRequest;

class ScrapeKitServiceProvider extends ServiceProvider {

    public function register() {
        $this->commands( [ MakeRequest::class ] );
    }
}
