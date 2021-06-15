<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Repository\ProductRepository;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    // protected $user;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->user = User::factory()->create();
    //     $this->actingAs($this->user, 'api');
    // }


    public function test_required_inputs()
    {
        // $this->withoutExceptionHandling();
        $this->json('POST', '/api/create-product')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'quantity' => ['The quantity field is required.'],
                    'amount' => ['The amount field is required.']
                ]
            ]);
    }

    public function test_unaunthenticated_user_cannot_create_product()
    {
        $data = [
            'name' => 'Mango Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
        ];

        $this->json('POST', '/api/create-product', $data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }


    public function test_a_product_can_be_created()
    {
        //     $product_name = (new ProductRepository()->createProduct(...));
        //     $this->assertEquals(expected:0, $product_name);
        // }
        $this->withoutExceptionHandling(); //display real error

        $data = [
            'name' => 'Mango Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
            // 'user_id' => Auth::id(),
        ];
        $user = User::factory()->create();
        $this->actingAs($user, 'api')
            ->json('POST', '/api/create-product', $data)
            ->assertOk()
            ->assertJson($data);

        $this->assertDatabaseHas('products', $data);
    }

    public function test_a_product_can_be_updated()
    {
        //     $product_name = (new ProductRepository()->createProduct(...));
        //     $this->assertEquals(expected:0, $product_name);
        // }
        $this->withoutExceptionHandling(); //display real error

        $product = Product::factory()->create([
            'name' => 'Orange Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
            // 'user_id' => Auth::id(),
        ]);

        $user = User::factory()->create();

        $quantity = 30;
        $updatedData = [
            'quantity' => $product['quantity'] + $quantity,
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/update-product?id=' . $product->id, $updatedData)
            ->assertOk()
            // ->assertJson($data);
            ->assertJson(['data' => $updatedData]);
    }

    public function test_getting_all_products()
    {
        $this->withoutExceptionHandling();
        $this->json('GET', '/api/products')
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'quantity',
                'sold',
                'active',
                'user_id',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_can_show_product_by_id()
    {
        $product = Product::factory()->create();

        $this->json('GET', '/api/single-product?id=' . $product->id)
            ->assertOk();
    }

    public function test_product_can_be_deleted()
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'api')->json('DELETE', '/api/delete-product?id=' . $product->id)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Product Deleted'
            ]);
    }

    public function test_a_product_can_be_mark_as_restock()
    {
        $this->withoutExceptionHandling(); //display real error

        $product = Product::factory()->create([
            'name' => 'Orange Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
        ]);

        $user = User::factory()->create();

        $quantity = 20;
        $updatedData = [
            'quantity' => $product['quantity'] + $quantity,
            // 'sold' => false,
            // 'active' => true
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/restock-product?id=' . $product->id, $updatedData)
            ->assertOk()
            ->assertJson(['data' => $updatedData]);
    }

    public function test_a_product_can_be_mark_as_sold()
    {
        $this->withoutExceptionHandling(); //display real error

        $product = Product::factory()->create([
            'name' => 'Orange Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
        ]);

        $user = User::factory()->create();

        $updatedData = [
            'quantity' => 0,
            'sold' => true,
            'active' => false
        ];

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/mark-as-sold?id=' . $product->id, $updatedData)
            ->assertOk()
            ->assertJson(['data' => $updatedData]);
    }
}