<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    // public function test_a_product_can_be_updated()
    // {
    //     //     $product_name = (new ProductRepository()->createProduct(...));
    //     //     $this->assertEquals(expected:0, $product_name);
    //     // }
    //     $this->withoutExceptionHandling(); //display real error

    //     $this->actingAs($user, 'api')
    //         ->json('PUT', '/api/update-product?id=' . $product->id, $updatedData)
    //         ->assertOk()
    //         // ->assertJson($data);
    //         ->assertJson(['data' => $updatedData]);
    // }

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

    public function test_transaction_can_be_mark_as_paid()
    {
        $product = Product::factory()->create();
        $transaction = Transaction::factory()->create([
            'product_id' => $product->id,
            'quantity' => 50,
            'total_amount' => $product->amount * 50,
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ]);
        $user = User::factory()->create();

        $markData = [
            'paid' => true
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/mark-as-paid?id=' . $transaction->id, $markData)
            ->assertOk()
            ->assertJson($markData);
    }
}