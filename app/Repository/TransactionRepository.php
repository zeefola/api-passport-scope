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
        $transaction = Transaction::where('user_id', Auth::id())
        ->where('id',$data['transaction_id'])->first();

        if(!$transaction){
            return ['message' => 'You\'re not Authorized'];
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
        $transaction = Transaction::where('user_id', Auth::id())
                          ->where('id',$data['transaction_id'])->first();
        $product = Product::where('id', $transaction->product_id)->first();

        if(!$transaction){
            return ['message' => 'You\'re not Authorized'];
        }

        $transaction->update([
            $transaction->confirmed = true
        ]);
        //Subtract Product quantity from product table
        $product->update([
            $product->quantity -= $transaction->quantity
        ]);

        return [
            'message' => 'Payment Confirmed',
            'data' => $transaction,
        ];
    }

    public function cancelTransaction($data)
    {
        $transaction = Transaction::where('user_id', Auth::id())
                          ->where('id',$data['transaction_id'])->first();
        $product = Product::where('id', $transaction->product_id)->first();

        if(!$transaction){
            return  ['message' => 'You\'re not Authorized'];
        }

        $transaction->update([
            $transaction->cancel = true
        ]);
        //Add Product quantity back to product table
        if($transaction->confirmed == true){
            $product->update([
                $product->quantity += $transaction->quantity
            ]);
        }

        return [
            'message' => 'Transaction Cancelled',
            'data' => $transaction,
        ];
    }

    public function getSingleTransaction($data)
    {
        $transaction = Transaction::where('user_id', Auth::id())
           ->where('id',$data['transaction_id'])->first();

        if(!$transaction)
        {
            return ['message' => 'Transaction Record not found'];
        }

        return [
            'data' => $transaction,
        ];
    }

    public function getAllTransaction()
    {
        return auth()->user()->transactions;
    }
}
