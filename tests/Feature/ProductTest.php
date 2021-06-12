<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_added_to_table()
    {
        $this->withoutExceptionHandling(); //display real error

        $response = $this->post('/api/create-product', [
            'name' => 'Mango Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
            // 'user_id' => Auth::id(),
       ]);

       $response->assertOk();
       $this->assertCount(1, Product::all());
    }
}
