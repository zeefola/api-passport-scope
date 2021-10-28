<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repository\ProductRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /** @var ProductRepository
     *
     */
    protected $product;

    /**
     * ProductController constructor
     * @param product $product
     */
    public function __construct(ProductRepository $product)
    {
        $this->product = $product;
    }

    /**
     * Create a new product
     * @param Request $request
     * @return JsonResponse
     */
    public function createProduct(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'name' => 'bail|required',
            'quantity' => 'bail|required',
            'amount' => 'bail|required',
        ])->validate();

        //Create product record
        return response()->json($this->product->createProduct($validatedData));
    }

    /**
     * Get all Product
     * @return JsonResponse
     */

    public function getAllProduct(): JsonResponse
    {
        return response()->json($this->product->getAllProduct());
    }

    /**
     * Get a single product
     * @param Request $request
     * @return JsonResponse
     */

    public function getSingleProduct(Request $request): JsonResponse
    {
        //Validate what's coming in
        $id = Validator::make($request->all(), ['id' => 'required'])->validate();
        return response()->json($this->product->getSingleProduct($id));
    }

    /**
     * Update product data
     * @param Request $request
     * @return JsonResponse
     */

    public function updateProduct(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required',
            'name' => 'bail|sometimes|required',
            'quantity' => 'bail|sometimes|required',
            'amount' => 'bail|sometimes|required',
            'sold' => 'bail|sometimes|required',
            'active' => 'bail|sometimes|required'
        ])->validate();

        $response = $this->product->updateProduct($validatedData);
        return response()->json($response);
    }

    /**
     * Delete a product record
     * @param Request $request
     * @return JsonResponse
     */

    public function deleteProduct(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required',
        ])->validate();

        $response = $this->product->deleteProduct($validatedData);
        return response()->json($response);
    }

    /**
     * Restock a particular product
     * @param Request $request
     * @return JsonResponse
     */

    public function restockProduct(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required',
            'quantity' => 'bail|required',
        ])->validate();

        $response = $this->product->restockProduct($validatedData);
        return response()->json($response);
    }

    /**
     * Mark a product as sold
     * @param Request $request
     * @return JsonResponse
     */

    public function markAsSold(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required',
        ])->validate();

        $response = $this->product->markAsSold($validatedData);
        return response()->json($response);
    }
}