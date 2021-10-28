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
        return response()->json($product);
        // return response()->json([
        //     'message' => 'Product Created Successfully',
        //     'product' => $product
        // ]);
    }

    /**
     * @return JsonResponse
     */

    public function getAllProduct(): JsonResponse
    {
        $product = $this->productrepository->getAllProduct();
        return response()->json(['products' => $product]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function getSingleProduct(Request $request): JsonResponse
    {
        //Validate what's coming in
        $id = Validator::make($request->all(), ['id' => 'required'])->validate();
        $product = $this->productrepository->getSingleProduct($id);

        return response()->json($product);
    }

    /**
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

        $response = $this->productrepository->updateProduct($validatedData);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function deleteProduct(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required',
        ])->validate();

        $response = $this->productrepository->deleteProduct($validatedData);
        return response()->json($response);
    }

    /**
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

        $response = $this->productrepository->restockProduct($validatedData);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function markAsSold(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'id' => 'bail|required',
        ])->validate();

        $response = $this->productrepository->markAsSold($validatedData);
        return response()->json($response);
    }
}