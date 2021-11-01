<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use App\Events\MarkAsPaid;
use App\Events\TransactionInitialised;
use App\Events\PaymentConfirmed;
use App\Events\PaymentRejected;
use App\Events\TransactionCancelled;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_transaction_required_inputs()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api')->json('POST', '/api/initialize-transaction')
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
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ];
        $user = User::factory()->create();
        $this->actingAs($user, 'api')
            ->json('POST', '/api/initialize-transaction', $data)
            ->assertOk()
            ->assertJson([
                'error' => true,
                'msg' => 'You can\'t initiate transaction on your product'
            ]);
    }

    public function test_transaction_doesnt_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api')
            ->json('PUT', '/api/mark-as-paid?transaction_id=3')
            ->assertJson([
                'error' => true,
                'msg' => 'Transaction Not Found'
            ]);
    }

    public function test_transaction_can_be_initialized()
    {
        Event::fake();
        $this->withoutExceptionHandling(); //display real error
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => 3,
            'quantity' => 50
        ]);
        Transaction::factory()->create();

        $transaction = [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10,
            'total_amount' => $product->amount * 10,
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ];


        $response = $this->actingAs($user, 'api')
            ->json('POST', '/api/initialize-transaction', $transaction)
            ->assertOk();
        Event::assertDispatched(TransactionInitialised::class);
        $response->assertJson([
            'error' => false,
            'msg' => 'Transaction Initialized. Check your inbox for the details',
            'data' => [
                'userId' => $user->id,
                'quantity' => 10
            ]
        ]);
    }

    public function test_transaction_can_be_mark_as_paid()
    {
        Event::fake();
        Product::factory()->create();
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->json('PUT', '/api/mark-as-paid?transaction_id=' . $transaction->id, [
                'paid' => true
            ]);

        // Event::assertDispatched(MarkAsPaid::class);

        $response->assertJson([
            'error' => false,
            'msg' => 'Marked as Paid',
            'mail' => 'Successfully Sent',
            'data' => [
                'paid' => true
            ]
        ]);
    }

    public function test_payment_can_be_confirmed()
    {
        Event::fake();

        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Mango Juice',
            'quantity' => 50,
            'amount' => 950,
            'sold' => false,
            'active' => true,
            'user_id' => $user->id,
        ]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween($min = 1, $max = 9000),
            'total_amount' => 10 * 50,
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ]);

        $response =  $this->actingAs($user, 'api')
            ->json('PUT', '/api/confirm-payment?transaction_id=' . $transaction->id, [
                'confirmed' => true,
            ])
            ->assertOk();

        Event::assertDispatched(PaymentConfirmed::class);
        $response->assertJson([
            "error" => false,
            "msg" =>  "Payment Confirmed, Mail Sent",
            "data" => [
                'confirmed' => true,
                "total_amount" => 500,
            ],

        ]);
    }

    public function test_payment_can_be_rejected()
    {
        $this->withoutExceptionHandling();

        Event::fake();

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

        $response = $this->actingAs($user, 'api')
            ->json('PUT', '/api/reject-payment?transaction_id=' . $transaction->id, [
                'paid' => false,
            ])
            ->assertOk();

        Event::assertDispatched(PaymentRejected::class);
        $response->assertJson([
            'error' => false,
            'msg' => 'Payment Rejected, Check your mail',
            'data' => [
                'paid' => false
            ]
        ]);
    }

    public function test_transaction_can_be_canceled()
    {
        Event::fake();
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

        $product->update([
            $product->quantity = $transaction->product->quantity + $transaction->quantity,
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('PUT', '/api/cancel-transaction?transaction_id=' . $transaction->id, [
                'cancel' => true,
            ])
            ->assertOk();

        Event::assertDispatched(TransactionCancelled::class);
        $response->assertJson([
            'error' => false,
            'msg' => 'Transaction Cancelled, Check your mail',
            'data' => [
                'paid' => false
            ]
        ]);
    }

    public function test_get_all_transaction()
    {

        $user = User::factory()->create();
        $transaction = Transaction::factory()->create();

        $this->actingAs($user, 'api')
            ->json('GET', '/api/transactions')
            ->assertOk()
            ->assertJson([
                'data' => array([
                    'userId' => $user->id,
                    'id' => $transaction->id
                ])
            ]);
    }

    public function test_get_user_transactions()
    {
        $user = User::factory()->create();

        Transaction::factory()->create(
            [
                'user_id' => $user->id,
                'product_id' => 1,
                'quantity' => 900,
                'total_amount' => 10 * 50,
                'paid' => false,
                'confirmed' => false,
                'cancel' => false,
            ]
        );
        $this->actingAs($user, 'api')
            ->json('GET', '/api/user-transactions?user_id=' . $user->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'userId' => $user->id,
                    'quantity' => 900
                ]
            ]);
    }

    public function test_can_show_transaction_by_id()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $transaction = Transaction::factory()->create();

        $this->actingAs($user, 'api')->json('GET', '/api/single-transaction?transaction_id=' . $transaction->id)
            ->assertOk()
            ->assertJson([
                'paid' => false,
                'id' => $transaction->id

            ]);
    }

    public function test_get_product_transactions()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $transaction = Transaction::factory()->create();

        $this->actingAs($user, 'api')
            ->json('GET', '/api/product-transactions?product_id=' . $product->id)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'productId' => $product->id,
                    'id' => $transaction->id
                ]
            ]);
    }
}