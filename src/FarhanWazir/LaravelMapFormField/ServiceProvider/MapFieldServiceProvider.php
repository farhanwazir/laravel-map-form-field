<?php namespace FarhanWazir\LaravelMapFormField\ServiceProvider;

use Illuminate\Support\ServiceProvider;

class MapFieldServiceProvider extends ServiceProvider {


    public function boot()
    {
        require '../MapField.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
    }
}
