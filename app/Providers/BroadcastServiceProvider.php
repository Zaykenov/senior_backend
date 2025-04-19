<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the routes needed to authorize broadcasting over WebSockets
        Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

        // Load channel authorization callbacks
        require base_path('routes/channels.php');
    }
}