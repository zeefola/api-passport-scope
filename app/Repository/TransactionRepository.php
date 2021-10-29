<?php

namespace App\Repository;

use App\Events\MarkAsPaid;
use App\Events\TransactionInitialised;
use App\Events\PaymentConfirmed;
use App\Events\PaymentRejected;
use App\Events\TransactionCancelled;
use App\Repository\Actors\ProductActor;
use App\Repository\Actors\UserActor;
use App\Repository\Actors\TransactionActor;

use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Transaction;
use App\Http\Resources\Transactions;

class TransactionRepository
{
    /**
     * @var ProductActor
     */
    private $product;
    /**
     * @var UserActor
     */
    private $user;
    /**
     * @var TransactionActor
     */
    private $transaction;

    /**
     * TransactionRepository constructor
     * @param TransactionActor $transaction
     * @param ProductActor $product
     * @param UserActor $user
     */
    public function __construct(TransactionActor $transaction, ProductActor $product, UserActor $user)
    {
        $this->transaction = $transaction;
        $this->user = $user;
        $this->product = $product;
    }
    /**
     * intialize transaction on a product
     * @param $data []
     * @return array
     */
    public function initializeTransaction($data): array
    {
        $product = $this->product->where('id', $data['product_id'])->first();
        $user = $this->product->where('user_id', Auth::id())
            ->where('id', $data['product_id'])->first();

        if (!$product) {
            return [
                'error' => true,
                'msg' => 'Product Not Found'
            ];
        }

        if ($user) {
            return [
                'error' => true,
                'msg' => 'You can\'t initiate transaction on your product'
            ];
        }
        //Create Transaction
        $data = $this->transaction->create([
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


        $userData = $this->user->where('id', $data['user_id'])->first();
        //store email data in an array and dispatch the event
        $email_data = [
            'mailTo' => $userData->email,
            'subject' => 'Transaction Initialized',
            'mail_body' => 'You\'re getting this mail because you successfully initialized a transaction for a product',

        ];
        event(new TransactionInitialised($email_data, $userData));
        Transaction::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Transaction Initialized. Check your inbox for the details',
            'data' => new Transaction($data)
        ];
    }
    /**
     * Mark transaction as paid
     * @param $data []
     * @return array
     */
    public function markAsPaid($data): array
    {
        $transaction = $this->transaction->where('id', $data['transaction_id'])->first();
        $user = $this->transaction->where('user_id', Auth::id())
            ->where('id', $data['transaction_id'])->first();

        if (!$transaction) {
            return [
                'error' => true,
                'msg' => 'Transaction Not Found'
            ];
        }

        if (!$user) {
            return [
                'error' => true,
                'msg' => 'You\'re Not Authorized'
            ];
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

        Transaction::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Marked as Paid',
            'mail' => 'Successfully Sent',
            'data' => new Transaction($transaction),
        ];
    }

    /**
     * Confirm payment
     * @param $data []
     * @return array
     */
    public function confirmPayment($data): array
    {
        $transaction = $this->transaction->find($data['transaction_id']);

        if (!$transaction) {
            return [
                'error' => true,
                'msg' => 'Transaction Not Found'
            ];
        }

        $user = $transaction->product->user_id;

        if ($user != Auth::id()) {
            return [
                'error' => true,
                'msg' => 'You\'re Not Authorized to Confirm the transaction'
            ];
        }

        $transaction->update([
            $transaction->confirmed = true
        ]);

        //Store mail data in an array and fire the event
        $userData = $this->user->where('id', $user)->first();
        $email_data = [
            'mailTo' => $userData->email,
            'subject' => 'Payment Confirmation',
            'mail_body' => 'This is to inform you your payment has been confirmed, thank you for trading with us'
        ];
        event(new PaymentConfirmed($email_data, $userData));

        Transaction::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Payment Confirmed, Mail Sent',
            'data' => new Transaction($transaction),
        ];
    }

    /**
     * Cancel transaction
     * @param $data
     * @return array
     */

    public function cancelTransaction($data): array
    {
        $transaction = $this->transaction->where('id', $data['transaction_id'])->first();
        $user = $this->transaction->where('user_id', Auth::id())
            ->where('id', $data['transaction_id'])->first();

        if (!$transaction) {
            return  [
                'error' => true,
                'msg' => 'Transaction Not Found'
            ];
        }
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'You\'re Not Authorized to Cancel the transaction'
            ];
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

        Transaction::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Transaction Cancelled, Check your mail',
            'data' => new Transaction($transaction),
        ];
    }

    /**
     * fetch a transaction record
     * @param $data
     * @return Transaction | array
     */
    public function getSingleTransaction($data): array
    {
        $transaction = $this->transaction->where('id', $data['transaction_id'])->first();

        if (!$transaction) {
            return [
                'error' => true,
                'msg' => 'Transaction Record Not found'
            ];
        }

        Transaction::withoutWrapping();
        return new Transaction($transaction);
    }

    /**
     * Get all transaction record that belongs to the logged in user
     * @return Transactions
     */
    public function getUserTransactions(): Transactions
    {
        $limit = request()->input('limit') ?? 25;
        $transactions = auth()->user()->transactions->simplePaginate($limit);
        return new Transactions($transactions);
    }

    /**
     * Get all transaction
     * @return Transactions
     */

    public function getAllTransaction(): Transactions
    {
        $limit = request()->input('limit') ?? 25;
        $transactions = $this->transaction->paginate($limit);
        return new Transactions($transactions);
    }
    /**
     * Get transaction records for a particular product
     * @param $data
     * @return Transactions
     */
    public function getProductTransactions($data): Transactions
    {
        $product = $this->product->find($data['product_id']);

        if (!$product) {
            return [
                'error' => true,
                'msg' => 'Product Record Not found'
            ];
        }
        $limit = request()->input('limit') ?? 25;
        $transactions = $product->transactions->paginate($limit);

        return new Transactions($transactions);
    }

    /**
     * Reject a payment
     * @param $data
     * @return array
     */

    public function rejectPayment($data): array
    {
        $transaction = $this->transaction->find($data['transaction_id']);

        if (!$transaction) {
            return [
                'error' => true,
                'msg' => 'Transaction Not Found'
            ];
        }

        $user = $transaction->product->user_id;
        if ($user != Auth::id()) {
            return [
                'error' => true,
                'msg' => 'You\'re Not Authorized to Reject the payment'
            ];
        }

        $transaction->update([
            $transaction->paid = false
        ]);

        //Store mail data in an array and fire the event
        $userData = $this->user->where('id', $user)->first();
        $email_data = [
            'mailTo' => $userData->email,
            'subject' => 'Payment Rejecttion',
            'mail_body' => 'This is to inform you your payment has been successfully rejected. Do check bac for me updates, thank you for trading with us'
        ];
        event(new PaymentRejected($email_data, $userData));

        Transaction::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Payment Rejected, Check your mail',
            'data' => new  Transaction($transaction),
        ];
    }
}