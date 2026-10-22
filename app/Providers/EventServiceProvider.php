<?php

namespace App\Providers;

use App\Events\PaymentReceived;
use App\Listeners\PaymentReceivedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentReceived::class => [
            PaymentReceivedListener::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
