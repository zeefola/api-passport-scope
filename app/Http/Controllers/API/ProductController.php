<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repository\ProductRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
            // 'sold' => 'bail|required',
            // 'active' => 'bail|required'
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
       $product = $this->productrepository->getAllProduct();
      return response()->json(['products' =>$product]);
    }

    public function getSingleProduct(Request $request)
    {
        //Validate what's coming in
        $id = Validator::make($request->all(),['id' => 'required'])->validate();
        $product = $this->productrepository->getSingleProduct($id);

        return response()->json(['Data' => $product]);
    }

    public function updateProduct(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required|exists:products,id',
            'name' => 'bail|sometimes|required',
            'quantity' => 'bail|sometimes|required',
            'amount' => 'bail|sometimes|required',
            'sold' => 'bail|sometimes|required',
            'active' => 'bail|sometimes|required'
        ])->validate();

        $response = $this->productrepository->updateProduct($validatedData);
        return response()->json($response);
    }

    public function deleteProduct(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required|exists:products,id',
        ])->validate();

        $response = $this->productrepository->deleteProduct($validatedData);
        return response()->json($response);
    }

    public function restockProduct(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required|exists:products,id',
            'quantity' => 'bail|required',
        ])->validate();

        $response = $this->productrepository->restockProduct($validatedData);
        return response()->json($response);
    }

    public function markAsSold(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required|exists:products,id',
        ])->validate();

        $response = $this->productrepository->markAsSold($validatedData);
        return response()->json($response);
    }
}
