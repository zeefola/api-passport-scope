<?php

namespace App\Listeners;

use App\Events\UserChangePassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\UserChangePasswordNotification;

class ChangePassword
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
     * @param  UserChangePassword  $event
     * @return void
     */
    public function handle(UserChangePassword $event)
    {
        $event->user->notify(new UserChangePasswordNotification($event->content));
    }
}