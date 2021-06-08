<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductRepository
{

    public function createProduct($productData)
    {
        return Product::create([
            'name' => $productData['name'],
            'quantity' => $productData['quantity'],
            'amount' => $productData['amount'],
            'sold' => false,
            'active' => true,
            'user_id' => Auth::id(),
            // 'user_id' => auth()->guard('api')->user()->id,
        ]);
    }

    public function getAllProduct()
    {
    }

    public function getSingleProduct()
    {
    }

    public function updateProduct()
    {
    }

    public function deleteProduct()
    {
    }

    public function restockProduct()
    {
    }

    public function markAsSold()
    {
    }
}