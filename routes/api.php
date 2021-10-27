<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);
//Register multiple user
Route::post('/multi-register', [AuthController::class, 'multiRegister']);
Route::post('/login', [AuthController::class, 'login']);

//Fetch Product
Route::get('/products', [ProductController::class, 'getAllProduct']);
Route::get('/single-product', [ProductController::class, 'getSingleProduct']);

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    //Product Routes
    Route::post('/create-product', [ProductController::class, 'createProduct']);
    Route::put('/update-product', [ProductController::class, 'updateProduct']);
    Route::delete('/delete-product', [ProductController::class, 'deleteProduct']);
    Route::put('/restock-product', [ProductController::class, 'restockProduct']);
    Route::put('/mark-as-sold', [ProductController::class, 'markAsSold']);

    //Transaction Route
    Route::post('/initialize-transaction', [TransactionController::class, 'initializeTransaction']);
    Route::put('/mark-as-paid', [TransactionController::class, 'markAsPaid']);
    Route::put('/confirm-payment', [TransactionController::class, 'confirmPayment']);
    Route::put('/reject-payment', [TransactionController::class, 'rejectPayment']);
    Route::put('/cancel-transaction', [TransactionController::class, 'cancelTransaction']);
    Route::get('/transactions', [TransactionController::class, 'getAllTransaction']);
    Route::get('/user-transactions', [TransactionController::class, 'getUserTransactions']);
    Route::get('/single-transaction', [TransactionController::class, 'getSingleTransaction']);
    Route::get('/product-transactions', [TransactionController::class, 'getProductTransactions']);
});