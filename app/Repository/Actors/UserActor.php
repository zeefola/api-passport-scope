<?php

namespace App\Repository\Actors;

use App\Models\User;
use App\Repository\Contracts\Repository;

class UserActor extends Repository
{

    public function __construct(User $user)
    {
        $this->model = $user;
    }
}