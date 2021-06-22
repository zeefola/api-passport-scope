<?php

namespace App\Listeners;

use App\Events\TransactionCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        //
    }
}
