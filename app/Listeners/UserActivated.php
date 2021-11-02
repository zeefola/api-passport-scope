<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendActivationMailNotification;

use App\Events\UserActivated;

class SendEmailNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  UserActivated  $event
     * @return void
     */
    public function handle(UserActivated $event)
    {
        //Send notification to the user
        $event->user->notify(new SendActivationMailNotification($event->content));
    }
}