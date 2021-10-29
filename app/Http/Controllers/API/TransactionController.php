<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repository\TransactionRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     *  @var TransactionRepository
     */
    protected $transaction;

    /**
     * TransactionController constructor
     * @param TransactionRepository $transaction
     */
    public function __construct(TransactionRepository $transaction)
    {
        $this->transaction = $transaction;
    }


    /**
     * Initialize transaction
     * @param Request $request
     * @return JsonResponse
     */
    public function initializeTransaction(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'product_id' => 'bail|required',
            'quantity' => 'bail|required',
        ])->validate();

        $response = $this->transaction->initializeTransaction($validatedData);
        return response()->json($response);
    }

    /**
     * Mark transaction as paid
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsPaid(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transaction->markAsPaid($validatedData);
        return response()->json($response);
    }

    /**
     * Confirm payment
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transaction->confirmPayment($validatedData);
        return response()->json($response);
    }

    /**
     * Reject payment
     * @param Request $request
     * @return JsonResponse
     */
    public function rejectPayment(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transaction->rejectPayment($validatedData);
        return response()->json($response);
    }

    /**
     * Cancal Transaction
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelTransaction(Request $request): JsonResponse
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transaction->cancelTransaction($validatedData);
        return response()->json($response);
    }

    /**
     * Get a transaction record
     * @param Request $request
     * @return JsonResponse
     */
    public function getSingleTransaction(Request $request): JsonResponse
    {
        //Validate what's coming in
        $validatedId = Validator::make(
            $request->all(),
            ['transaction_id' => 'required']
        )->validate();

        $transaction = $this->transaction->getSingleTransaction($validatedId);

        return response()->json($transaction);
    }


    /**
     * Get all transaction
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllTransaction(): JsonResponse
    {
        $transactions = $this->transaction->getAllTransaction();
        return response()->json($transactions);
    }


    /**
     * Get transaction records for a logged in user
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserTransactions(): JsonResponse
    {
        $transactions = $this->transaction->getUserTransactions();
        return response()->json($transactions);
    }


    /**
     * Get transaction records for a particular product
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductTransactions(Request $request): JsonResponse
    {
        //Validate what's coming in
        $validatedId = Validator::make(
            $request->all(),
            ['product_id' => 'required']
        )->validate();

        $transactions = $this->transaction->getProductTransactions($validatedId);

        return response()->json($transactions);
    }
}