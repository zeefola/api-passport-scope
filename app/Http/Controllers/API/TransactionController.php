<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repository\TransactionRepository;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected $transactionrepository;

    public function __construct(TransactionRepository $transactionrepository)
    {
        $this->transactionrepository = $transactionrepository;
    }

    public function initializeTransaction(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'product_id' => 'bail|required',
            'quantity' => 'bail|required',
        ])->validate();

        $response = $this->transactionrepository->initializeTransaction($validatedData);
        return response()->json($response);
    }

    public function markAsPaid(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transactionrepository->markAsPaid ($validatedData);
        return response()->json($response);
    }

    public function confirmPayment(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transactionrepository->confirmPayment($validatedData);
        return response()->json($response);
    }

    public function cancelTransaction(Request $request)
    {
        //Validate inputs
        $validatedData = Validator::make($request->all(), [
            'transaction_id' => 'bail|required',
        ])->validate();

        $response = $this->transactionrepository->cancelTransaction($validatedData);
        return response()->json($response);
    }

    public function getSingleTransaction(Request $request)
    {
        //Validate what's coming in
        $validatedId = Validator::make($request->all(),
        ['transaction_id' => 'required'])->validate();

        $transaction = $this->transactionrepository->getSingleTransaction($validatedId);

        return response()->json($transaction);
    }

    public function getAllTransaction()
    {
        $transaction = $this->transactionrepository->getAllTransaction();
        return response()->json(['transactions' =>$transaction]);
    }
}
