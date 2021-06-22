<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        'App\Events\UserRegistered' => [
            'App\Listeners\SendEmailNotification',
        ],

        'App\Events\TransactionInitialised' => [
            'App\Listeners\SendInitilizationNotification',
        ],

        'App\Events\MarkAsPaid' => [
            'App\Listeners\SendPaidNotification',
        ],

        'App\Events\PaymentConfirmed' => [
            'App\Listeners\SendPaymentConfirmationNotification',
        ],

        'App\Events\PaymentRejected' => [
            'App\Listeners\SendPaymentRejectionNotification',
        ],

        'App\Events\TransactionCancelled' => [
            'App\Listeners\SendCancelledTransactionNotification',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}