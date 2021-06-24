<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendPaymentConfirmationMailNotification;

class SendPaymentConfirmationNotification
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
     * @param  PaymentConfirmed  $event
     * @return void
     */
    public function handle(PaymentConfirmed $event)
    {
        $event->userData->notify(new SendPaymentConfirmationMailNotification($event->email_data));
    }
}