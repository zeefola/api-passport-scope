<?php

namespace App\Listeners;

use App\Events\MarkAsPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendProductMarkAsPaidMailNotification;

class SendPaidNotification
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
     * @param  MarkAsPaid  $event
     * @return void
     */
    public function handle(MarkAsPaid $event)
    {
        $event->user->notify(new SendProductMarkAsPaidMailNotification($event->email_data));
    }
}