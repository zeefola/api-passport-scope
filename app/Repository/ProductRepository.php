<?php

namespace App\Repository;

use App\Repository\Actors\ProductActor;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Product;
use App\Http\Resources\Products;

class ProductRepository
{

    /**
     * @var ProductActor
     * private $product
     */

    /**
     * ProductRepository constructor
     * @param ProductActor $product
     */

    public function __construct(ProductActor $product)
    {
        $this->product = $product;
    }

    /**
     * Create new product record
     * @param $productData
     * @return array
     */

    public function createProduct($productData): array
    {
        //Check if product name already exists
        $db_data = $this->product->where('name', $productData['name'])->exists();

        if ($db_data) {
            return [
                'error' => true,
                'msg' => 'Product Name Already exists'
            ];
        }

        $this->product->create([
            'name' => $productData['name'],
            'quantity' => $productData['quantity'],
            'amount' => $productData['amount'],
            'sold' => false,
            'active' => true,
            'user_id' => Auth::id(),
        ]);

        $product = $this->product->findBy('name', $productData['name']);

        Product::withoutWrapping();

        return [
            'error' => false,
            'msg' => 'Product Created Successfully',
            'data' => new Product($product)
        ];
    }

    /** Get all Product
     * @return Products
     */

    public function getAllProduct(): Products
    {
        $limit = request()->input('imit') ?? 25;
        $products = $this->product->where('sold', false)->paginate($limit);

        return new Products($products);
    }

    /** Get a product record
     * @param $id
     * @return Product | array
     */

    public function getSingleProduct($id)
    {
        $product = $this->product->where('id', $id)->first();

        if (!$product) {
            return [
                'error' => true,
                'msg' => 'Product Not Found'
            ];
        }

        Product::withoutWrapping();
        return new Product($product);
    }
    /**
     * Update product information
     * @param $data
     * @retun array
     */
    public function updateProduct($data): array
    {
        $product = $this->product->where('id', $data['id'])->first();
        $user = $this->product->where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return [
                'error' => true,
                'msg' => 'Product Not Found'
            ];
        }

        if (!$user) {
            return [
                'error' => true,
                'msg' =>  'You\'re not Authorized'
            ];
        }

        $product->update($data);
        Product::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Product Updated',
            'data' => new Product($product),
        ];
    }

    /** Delete a product
     * @param $data
     * @return array
     */

    public function deleteProduct($data): array
    {
        $product = $this->product->where('id', $data['id'])->first();
        $user = $this->product->where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return [
                'error' => true,
                'msg' => 'Product Not Found'
            ];
        }

        if (!$user) {
            return [
                'error' => true,
                'msg' => 'You\'re not Authorized'
            ];
        }

        if ($product->sold == true) {
            return [
                'error' => true,
                'msg' => 'You can\'t delete a sold out product'
            ];
        }

        $product->delete($data);
        return  [
            'error' => false,
            'msg' => 'Product Deleted',
            'data' => []
        ];
    }

    /** Restock a particular product
     * @param $data
     * @return array
     */

    public function restockProduct($data): array
    {
        $product = $this->product->where('id', $data['id'])->first();
        $user = $this->product->where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return [
                'error' => true,
                'msg'  => 'Product Not Found'
            ];
        }

        if (!$user) {
            return [
                'error' => true,
                'msg' => 'You\'re not Authorized'
            ];
        }

        $product->update([
            $product->quantity += $data['quantity'],
            $product->sold = false,
            $product->active = true
        ]);
        Product::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Product Restocked',
            'data' => new Product($product),
        ];
    }

    /** Mark a product as sold out
     * @param $data
     * @return array
     */

    public function markAsSold($data): array
    {
        $product = $this->product->where('id', $data['id'])->first();
        $user = $this->product->where('user_id', Auth::id())
            ->where('id', $data['id'])->first();

        if (!$product) {
            return [
                'error' => true,
                'msg' => 'Product Not Found'
            ];
        }

        if (!$user) {
            return [
                'error' => true,
                'msg' => 'You\'re not Authorized'
            ];
        }

        if ($product->sold == true) {
            return [
                'error' => true,
                'msg' => 'Product is already sold out'
            ];
        }

        $product->update([
            $product->quantity = 0,
            $product->sold = true,
            $product->active = false
        ]);

        Product::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Marked as Sold',
            'data' => new Product($product)

        ];
    }
}