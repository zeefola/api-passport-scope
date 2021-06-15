<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductRepository
{

    public function createProduct($productData)
    {
        //Check if product name already exists
        $db_data = Product::where('name', $productData['name'])->exists();

        if ($db_data) {
            return ['error' => 'Product Name Already exists'];
        }

        return Product::create([
            'name' => $productData['name'],
            'quantity' => $productData['quantity'],
            'amount' => $productData['amount'],
            'sold' => false,
            'active' => true,
            'user_id' => Auth::id(),
        ]);
    }

    public function getAllProduct()
    {
        return Product::where('sold', false)->get();
    }

    public function getSingleProduct($id)
    {
        $product = Product::where('id', $id)->first();

        if (!$product) {
            return ['error' => 'Product Not Found'];
        }

        return ['data' => $product];
    }

    public function updateProduct($data)
    {
        // $result = [];
        $product = Product::where('id', $data['id'])->first();
        $user = Product::where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return ['error' => 'Product Not Found'];
        }

        if (!$user) {
            return ['error' => 'You\'re not Authorized'];
        }

        $product->update($data);
        return [
            'message' => 'Product Updated',
            'data' => $product
        ];

        // if($data['name']){
        //     $result['name'] = $data['name'];
        // }
    }

    public function deleteProduct($data)
    {
        // $product = Product::find($data['id']);
        $product = Product::where('id', $data['id'])->first();
        $user = Product::where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return ['error' => 'Product Not Found'];
        }

        if (!$user) {
            return ['error' => 'You\'re not Authorized'];
        }

        if ($product->sold == true) {
            return ['error' => 'You can\'t delete a sold out product'];
        }

        $product->delete($data);
        return  [
            'message' => 'Product Deleted',
            'data' => []
        ];
    }

    public function restockProduct($data)
    {
        $product = Product::where('id', $data['id'])->first();
        $user = Product::where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return ['error' => 'Product Not Found'];
        }

        if (!$user) {
            return ['error' => 'You\'re not Authorized'];
        }

        $product->update([
            $product->quantity += $data['quantity'],
            $product->sold = false,
            $product->active = true
        ]);

        return [
            'message' => 'Product Restocked',
            'data' => $product
        ];
    }

    public function markAsSold($data)
    {
        $product = Product::where('id', $data['id'])->first();
        $user = Product::where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return ['error' => 'Product Not Found'];
        }

        if (!$user) {
            return ['error' => 'You\'re not Authorized'];
        }

        if ($product->sold == true) {
            return ['error' => 'Product is already sold out'];
        }

        $product->update([
            $product->quantity = 0,
            $product->sold = true,
            $product->active = false
        ]);
        return [
            'message' => 'Marked as Sold',
            'data' => $product
        ];
    }
}