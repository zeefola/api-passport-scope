<?php

namespace App\Repository\Actors;

use App\Models\Transaction;
use App\Repository\Contracts\Repository;

class ProductActor extends Repository
{

    public function __construct(Transaction $transaction)
    {
        $this->model = $transaction;
    }
}