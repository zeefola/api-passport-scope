<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendRegisterMailNotification;
use App\Events\UserRegistered;
use App\Models\User;


class SendEmailNotification
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
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        // Fetch User from the event
        $user = User::where('email', $event->email_data['mailTo'])
            ->first();

        //Send notification to the user
        $user->notify(new SendRegisterMailNotification($event->email_data));
    }
}