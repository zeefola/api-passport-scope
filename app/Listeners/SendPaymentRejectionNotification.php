<?php

namespace App\Listeners;

use App\Events\PaymentRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendPaymentRejectionMailNotification;

class SendPaymentRejectionNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaymentRejected  $event
     * @return void
     */
    public function handle(PaymentRejected $event)
    {
        $event->userData->notify(new SendPaymentRejectionMailNotification($event->email_data));
    }
}