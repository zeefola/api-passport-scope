<?php

namespace App\Repository\Actors;

use App\Models\Product;
use App\Repository\Contracts\Repository;

class ProductActor extends Repository
{

    public function __construct(Product $product)
    {
        $this->model = $product;
    }
}