<?php

namespace App\Providers;

use App\Events\ClientCreated;
use App\Events\LoanAccountPayment;
use App\Events\LoanAccountPaymentEvent;
use App\Events\TestEvent;
use App\Listeners\ClientCreated as ListenOnClientCreate;
use App\Listeners\LoanAccountPaymentListener;
use App\Listeners\TestListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.e
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // ClientCreated::class =>[
        //     ListenOnClientCreate::class,
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
