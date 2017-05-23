<?php namespace Sukohi\BackThen;

use Illuminate\Support\ServiceProvider;

class BackThenServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var  bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'migration');
    }
}