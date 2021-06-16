<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_required_inputs()
    {
        $user = User::factory()->create();
        $this->actingAs($user,'api')->json('POST', '/api/initialize-transaction')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'product_id' => ['The product id field is required.'],
                    'quantity' => ['The quantity field is required.'],
                ]
            ]);
    }


    public function test_product_owner_cant_initialize_transaction()
    {
        $this->withoutExceptionHandling(); //display real error
        $product = Product::factory()->create();
        $data = [
            'product_id' => $product->id,
            'quantity' => 10,
            'total_amount' => $product->amount * 10,
            // 'total_amount' => $product->amount * $data['quantity'],
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ];
        $user = User::factory()->create();
        $this->actingAs($user, 'api')
            ->json('POST', '/api/initialize-transaction', $data)
            ->assertOk()
            ->assertJson(['message' => 'You can\'t initiate transaction on your product']);

        // $this->assertDatabaseHas('transactions', $data);
    }

    public function test_transaction_can_be_initialized()
    {
        $this->withoutExceptionHandling(); //display real error
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Orange',
            'quantity' => 10,
            'amount' => 50,
            'sold' => false,
            'active' => true,
            'user_id' => 1,
        ]);

        $transaction = [
            'product_id' => $product->id,
            'quantity' => 10,
            'total_amount' => $product->amount * 10,
            // 'total_amount' => $product->amount * $data['quantity'],
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ];

        $this->actingAs($user, 'api')
            ->json('POST', '/api/initialize-transaction', $transaction)
            ->assertOk()
            ->assertJson($transaction);

        // $this->assertDatabaseHas('transactions', $data);
    }

    public function test_transaction_can_be_mark_as_paid()
    {
        $product = Product::factory()->create();
        $user = User::create(
            ['name' => 'zainab',
            'email' => 'zeefo@gmail.com',
            'scopes' => ["user", "all", "products"],
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),]
        );
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'total_amount' => $product->amount * 50,
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ]);

        $markData = [
            'paid' => true
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/mark-as-paid?transaction_id=' . $transaction->id, $markData)
            // ->assert($transaction->id);
            ->assertJson([
                'message' => 'Marked as Paid',
                'data' => $markData
                ]);
    }

    public function test_payment_can_be_confirmed()
    {
        $this->withoutExceptionHandling();

        $product = Product::factory()->create();
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'total_amount' => $product->amount * 50,
            'paid' => true,
            'confirmed' => false,
            'cancel' => false,
        ]);

        $confirmPayment = [
            'confirmed' => true,
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/confirm-payment?transaction_id=' . $transaction->id, $confirmPayment)
            ->assertOk()
            ->assertJson([
                'message' => 'Payment Confirmed',
                'data' => $confirmPayment]);
    }

    public function test_payment_can_be_rejected()
    {
        $this->withoutExceptionHandling();

        $product = Product::factory()->create();
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'total_amount' => $product->amount * 50,
            'paid' => true,
            'confirmed' => false,
            'cancel' => false,
        ]);

        $rejectPayment = [
            'paid' => false,
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/reject-payment?transaction_id=' . $transaction->id, $rejectPayment)
            ->assertOk()
            ->assertJson([
                'message' => 'Payment Rejected',
                'data' => $rejectPayment]);
    }

    public function test_transaction_can_be_canceled()
    {
        $this->withoutExceptionHandling();

        $product = Product::factory()->create();
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'total_amount' => $product->amount * 50,
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ]);

        $cancelTransaction = [
            'cancel' => true,
        ];

         $product->update ([
                  $product->quantity = $transaction->product->quantity + $transaction->quantity,
        ]);

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/cancel-transaction?transaction_id=' . $transaction->id, $cancelTransaction)
            ->assertOk()
            ->assertJson([
                'message' => 'Transaction Cancelled',
                'data' => $cancelTransaction]);
    }

    public function test_get_all_transaction(){

        $user = User::factory()->create();
        $transaction = Transaction::factory()->count(3)->create();

        $this->actingAs($user, 'api')
        ->json('GET', '/api/transactions')
        ->assertOk()
        ->assertJson($transaction);
    }

    public function test_get_user_transactions(){
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(
            [
                'user_id' => 1,
                'product_id' => 1,
                'quantity' => 900,
                'total_amount' => 10 * 50,
                'paid' => false,
                'confirmed' => false,
                'cancel' => false,
            ]
        );
        $this->actingAs($user, 'api')
        ->json('GET', '/api/user-transactions')
        ->assertStatus(200)
        ->assertJson(['transactions' => $transaction]);
    }

    public function test_can_show_transaction_by_id()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $transaction = Transaction::factory()->create();

        $this->actingAs($user, 'api')->json('GET', '/api/single-transaction?transaction_id=' . $transaction->id)
            ->assertOk();
            // ->assertJson($transaction);
    }

    public function test_get_product_transactions(){
     $user = User::factory()->create();
     $product = Product::factory()->create();
     $transaction = Transaction::factory()->create();

     $this->actingAs($user, 'api')
     ->json('GET', '/api/product-transactions?product_id='. $product->id)
     ->assertOk();
    }
}
