<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repository\ProductRepository;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $productrepository;

    public function __construct(ProductRepository $productrepository)
    {
        $this->productrepository = $productrepository;
    }

    public function createProduct(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'name' => 'bail|required',
            'quantity' => 'bail|required',
            'amount' => 'bail|required',
        ])->validate();

        //Create product record
        $product = $this->productrepository->createProduct($validatedData);
        return response()->json([
            'message' => 'Product Created Successfully',
            'product' => $product
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