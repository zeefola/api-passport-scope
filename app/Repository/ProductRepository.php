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
        return Product::where('sold',false)->get();
    }

    public function getSingleProduct($id)
    {
      return Product::findOrFail($id);
    }

    public function updateProduct($data)
    {
        // $result = [];
        $product = Product::where('user_id', Auth::id())
               ->where('id',$data['id'])->first();

        if(!$product){
            return $data = ['message' => 'You\'re not Authorized'];
        }

        $product->update($data);
        return $data = [
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
        $product = Product::where('user_id',Auth::id())
            ->where('id',$data['id'])
                   ->where('sold',false)->first();

        if(!$product){
            return $data = ['message' => 'You\'re not Authorized'];
        }

        $product->delete($data);
        return $data = [
        'message' => 'Product Deleted',
        'data' => []
        ];
    }

    public function restockProduct($data)
    {
        $product = Product::where('user_id', Auth::id())
               ->where('id',$data['id'])->first();

        if(!$product){
            return $data = ['message' => 'You\'re not Authorized'];
        }

        if($product->sold == false) {
            return $data = ['message' => 'Can\'t restock product thats still in stock'];
        }

        if($product->sold == true) {
            $product->update([
                $product->quantity = $data['quantity'],
                $product->sold = false,
                $product->active = true
            ]);

            return $data = [
            'message' => 'Product Restocked',
            'data' => $product
            ];
        }
    }

    public function markAsSold($data)
    {
        $product = Product::where('user_id', Auth::id())
               ->where('id',$data['id'])->first();

        if(!$product){
            return $data = ['message' => 'You\'re not Authorized'];
        }

        if($product->sold == false){
            $product->update([
                $product->quantity = 0,
                $product->sold = true,
                $product->active = false
            ]);
            return $data = [
                'message' => 'Marked as Sold',
                'data' => $product
            ];
        }
    }
}
