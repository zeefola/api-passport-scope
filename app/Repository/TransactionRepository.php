<?php

namespace App\Repository;

use App\Events\MarkAsPaid;
use App\Events\TransactionInitialised;
use App\Events\PaymentConfirmed;
use App\Events\PaymentRejected;
use App\Events\TransactionCancelled;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TransactionRepository
{
    public function initializeTransaction($data)
    {
        $product = Product::where('id', $data['product_id'])->first();
        $user = Product::where('user_id', Auth::id())
            ->where('id', $data['product_id'])->first();

        if (!$product) {
            return ['message' => 'Product Not Found'];
        }

        if ($user) {
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


        $userData = User::where('id', $data['user_id'])->first();
        //store email data in an array and dispatch the event
        $email_data = [
            'mailTo' => $userData->email,
            'subject' => 'Transaction Initialized',
            'mail_body' => 'You\'re getting this mail because you successfully initialized a transaction for a product',

        ];
        event(new TransactionInitialised($email_data, $userData));

        return [
            'message' => 'Transaction Initialized',
            'data' => $data,
            'mail' => 'Mail Sent, Check your inbox'
        ];
    }

    public function markAsPaid($data)
    {
        $transaction = Transaction::where('id', $data['transaction_id'])->first();
        $user = Transaction::where('user_id', Auth::id())
            ->where('id', $data['transaction_id'])->first();

        if (!$transaction) {
            return ['message' => 'Transaction Not Found'];
        }

        if (!$user) {
            return ['message' => 'You\'re Not Authorized'];
        }

        $transaction->update([
            $transaction->paid = true
        ]);

        //Store mail data in an array and fire the event
        $user = $transaction->user;
        $email_data = [
            'mailTo' => $user->email,
            'subject' => 'Transaction Mark As Paid',
            'mail_body' => 'This is to inform you your transaction has been marked as paid, thank you for trading with us'
        ];
        event(new MarkAsPaid($email_data, $user));

        return [
            'message' => 'Marked as Paid',
            'mail' => 'Successfully Sent',
            'data' => $transaction,
        ];
    }

    public function confirmPayment($data)
    {
        $transaction = Transaction::find($data['transaction_id']);

        if (!$transaction) {
            return ['error' => 'Transaction Not Found'];
        }

        $user = $transaction->product->user_id;

        if ($user != Auth::id()) {
            return ['error' => 'You\'re Not Authorized to Confirm the transaction'];
        }

        $transaction->update([
            $transaction->confirmed = true
        ]);

        //Store mail data in an array and fire the event
        $userData = User::where('id', $user)->first();
        $email_data = [
            'mailTo' => $userData->email,
            'subject' => 'Payment Confirmation',
            'mail_body' => 'This is to inform you your payment has been confirmed, thank you for trading with us'
        ];
        event(new PaymentConfirmed($email_data, $userData));

        return [
            'message' => 'Payment Confirmed, Mail Sent',
            'data' => $transaction,
        ];
    }

    public function cancelTransaction($data)
    {
        $transaction = Transaction::where('id', $data['transaction_id'])->first();
        $user = Transaction::where('user_id', Auth::id())
            ->where('id', $data['transaction_id'])->first();

        if (!$transaction) {
            return  ['message' => 'Transaction Not Found'];
        }
        if (!$user) {
            return ['error' => 'You\'re Not Authorized to Cancel the transaction'];
        }

        $transaction->update([
            $transaction->cancel = true
        ]);
        //Add Product quantity back to product table
        if ($transaction->confirmed == true) {
            $transaction->product->quantity += $transaction->quantity;
            $transaction->product->save();
        }

        //Store mail data in an array and fire the event
        $user = $transaction->user;
        $email_data = [
            'mailTo' => $user->email,
            'subject' => 'Transaction Cancelled',
            'mail_body' => 'This is to inform you your transaction has been successfully cancelled, thank you for trading with us'
        ];
        event(new TransactionCancelled($email_data, $user));

        return [
            'message' => 'Transaction Cancelled, Check your mail',
            'data' => $transaction,
        ];
    }

    public function getSingleTransaction($data)
    {
        $transaction = Transaction::where('id', $data['transaction_id'])->first();

        if (!$transaction) {
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

        if (!$product) {
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

        if (!$transaction) {
            return ['error' => 'Transaction Not Found'];
        }

        $user = $transaction->product->user_id;
        if ($user != Auth::id()) {
            return ['error' => 'You\'re Not Authorized to Reject the payment'];
        }

        $transaction->update([
            $transaction->paid = false
        ]);

        //Store mail data in an array and fire the event
        $userData = User::where('id', $user)->first();
        $email_data = [
            'mailTo' => $userData->email,
            'subject' => 'Payment Rejecttion',
            'mail_body' => 'This is to inform you your payment has been successfully rejected. Do check bac for me updates, thank you for trading with us'
        ];
        event(new PaymentRejected($email_data, $userData));

        return [
            'message' => 'Payment Rejected, Check your mail',
            'data' => $transaction,
        ];
    }
}