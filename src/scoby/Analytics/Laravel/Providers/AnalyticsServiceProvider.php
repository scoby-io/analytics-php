<?php namespace Scoby\Analytics\Laravel\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Scoby\Analytics\Client;

/**
 * Class PasswordServiceProvider
 */
class AnalyticsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function () {
            $jarId = env('SCOBY_JAR_ID');
            if(!$jarId) {
                throw new \Exception('Cannot initialize scoby analytics without $jarId. Please set env variable SCOBY_JAR_ID');
            }
            return new Client($jarId);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Client::class];
    }
}
