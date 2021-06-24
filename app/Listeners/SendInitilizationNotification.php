<?php

namespace App\Listeners;

use App\Events\TransactionInitialised;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendInitialiseTransactionMailNotification;
// use App\Models\User;

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
        // $user = User::where('id', $event->email_data['user_id'])->first();

        $event->userData->notify(new SendInitialiseTransactionMailNotification($event->email_data));
    }
}