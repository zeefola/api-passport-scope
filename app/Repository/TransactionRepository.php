<?php

namespace App\Repository;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionRepository
{
    public function initializeTransaction($data)
    {
        $product = Product::where('user_id', Auth::id())
               ->where('id',$data['product_id'])->first();

        if(!$product){
            return $data = ['message' => 'You\'re not Authorized'];
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

        return $data = [
            'message' => 'Transaction Initialized',
            'data' => $data
        ];
    }

    public function markAsPaid($data)
    {
        $transaction = Transaction::where('user_id', Auth::id())
        ->where('id',$data['transaction_id'])->first();

        if(!$transaction){
            return $data = ['message' => 'You\'re not Authorized'];
        }

        $transaction->update([
         $transaction->paid = true
        ]);

        return $data = [
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
            return $data = ['message' => 'You\'re not Authorized'];
        }

        $transaction->update([
            $transaction->confirmed = true
        ]);
        //Subtract Product quantity from product table
        $product->update([
            $product->quantity -= $transaction->quantity
        ]);

        return $data = [
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
            return $data = ['message' => 'You\'re not Authorized'];
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

        return $data = [
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
            return $data = ['message' => 'Transaction Record not found'];
        }

        return $data = [
            'data' => $transaction,
        ];
    }

    public function getAllTransaction()
    {
        return auth()->user()->transactions;
    }
}
