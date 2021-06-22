<?php

namespace App\Listeners;

use App\Events\TransactionInitialised;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInitilizationNotification
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
     * @param  TransactionInitialised  $event
     * @return void
     */
    public function handle(TransactionInitialised $event)
    {
        //
    }
}
