<?php

namespace App\Listeners;

use App\Events\TransactionCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendCancelTransactionMailNotification;

class SendCancelledTransactionNotification
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
     * @param  TransactionCancelled  $event
     * @return void
     */
    public function handle(TransactionCancelled $event)
    {
        $event->user->notify(new SendCancelTransactionMailNotification($event->email_data));
    }
}