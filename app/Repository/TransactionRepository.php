<?php

namespace App\Repository;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionRepository
{
    public function initializeTransaction($data)
    {
        $product = Product::where('id',$data['product_id'])->first();
        $user = Product::where('user_id', Auth::id())
           ->where('id',$data['product_id'])->first();

        if(!$product){
            return ['message' => 'Product Not Found'];
        }

        if($user){
            return ['message' => 'You can\'t initiate transaction on your product'];
        }
        //Create Transaction
        $data = Transaction::create([
            'user_id' => Auth::id(),
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'total_amount' => $product->amount * $data['quantity'],
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ]);
        //Subtract Product quantity from product table
        $product->update([
            $product->quantity -= $data['quantity'],
        ]);


        return [
            'message' => 'Transaction Initialized',
            'data' => $data
        ];
    }

    public function markAsPaid($data)
    {
        $transaction = Transaction::where('id',$data['transaction_id'])->first();
        $user = Transaction::where('user_id', Auth::id())
            ->where('id',$data['transaction_id'])->first();

        if(!$transaction){
            return ['message' => 'Transaction Not Found'];
        }

        if(!$user){
            return ['message' => 'You\'re Not Authorized'];
        }

        $transaction->update([
         $transaction->paid = true
        ]);

        return [
            'message' => 'Marked as Paid',
            'data' => $transaction,
        ];
    }

    public function confirmPayment($data)
    {
        $transaction = Transaction::find($data['transaction_id']);

        if(!$transaction){
            return ['error' => 'Transaction Not Found'];
        }

        $user = $transaction->product->user_id;
        if($user != Auth::id()){
            return ['error' => 'You\'re Not Authorized to Confirm the transaction'];
        }

        $transaction->update([
            $transaction->confirmed = true
        ]);

        return [
            'message' => 'Payment Confirmed',
            'data' => $transaction,
        ];
    }

    public function cancelTransaction($data)
    {
        $transaction = Transaction::where('id',$data['transaction_id'])->first();
        $user = Transaction::where('user_id', Auth::id())
            ->where('id',$data['transaction_id'])->first();

        if(!$transaction){
            return  ['message' => 'Transaction Not Found'];
        }
        if(!$user){
            return ['error' => 'You\'re Not Authorized to Cancel the transaction'];
        }

        $transaction->update([
            $transaction->cancel = true
        ]);
        //Add Product quantity back to product table
        if($transaction->confirmed == true){
            $transaction->product->quantity += $transaction->quantity;
            $transaction->product->save();
        }

        return [
            'message' => 'Transaction Cancelled',
            'data' => $transaction,
        ];
    }

    public function getSingleTransaction($data)
    {
        $transaction = Transaction::where('id',$data['transaction_id'])->first();

        if(!$transaction)
        {
            return ['message' => 'Transaction Record Not found'];
        }

        return [
            'data' => $transaction,
        ];
    }

    public function getUserTransactions()
    {
        return auth()->user()->transactions;
    }

    public function getAllTransaction()
    {
        return Transaction::all();
    }

    public function getProductTransactions($data)
    {
        $product = Product::find($data['product_id']);

        if(!$product)
        {
            return ['message' => 'Product Record Not found'];
        }

        $transactions = $product->transactions;
        // if($transactions != $product)
        // {
        //     return ['message' => 'Transaction Record Not found'];
        // }

        return [
            'data' => $transactions,
        ];
    }

    public function rejectPayment($data)
    {
        $transaction = Transaction::find($data['transaction_id']);

        if(!$transaction){
            return ['error' => 'Transaction Not Found'];
        }

        $user = $transaction->product->user_id;
        if($user != Auth::id()){
            return ['error' => 'You\'re Not Authorized to Reject the payment'];
        }

        $transaction->update([
            $transaction->paid = false
        ]);

        return [
            'message' => 'Payment Rejected',
            'data' => $transaction,
        ];
    }
}
