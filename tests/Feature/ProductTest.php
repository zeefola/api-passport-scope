<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Repository\ProductRepository;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    // protected $user;

<<<<<<< HEAD
    public function setUp(): void
    {
        parent::setUp();
=======
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
>>>>>>> 40edd22be4752f0744821fcfc8aebfc547840780

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

        $user = User::factory()->create();

        $data = [
            'name' => 'Mango Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
            'user_id' => Auth::id(),
        ];

        $this->actingAs($user, 'api')
            ->json('POST', '/api/create-product', $data)
            ->assertOk()
            ->assertDatabaseHAs('products', $data)
            ->assertJson($data);
        // ->assertJsonStructure(
        //     [
        //         'name',
        //         'quantity',
        //         'amount',
        //         'sold',
        //         'active',
        //         'user_id',
        //         'updated_at',
        //         'created_at',
        //         'id',
        //     ]
        // );
    }

    public function test_a_product_can_be_updated()
    {
        //     $product_name = (new ProductRepository()->createProduct(...));
        //     $this->assertEquals(expected:0, $product_name);
        // }
        $this->withoutExceptionHandling(); //display real error

        $product = Product::factory()->make();
        $this->user->products()->save($product);

        $updatedData = [
            'name' => 'Mango rinkD',
            'quantity' => 90,
        ];

        $this->json('PUT', '/api/update-product?id=' . $product->id, $updatedData)
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
        $product = Product::factory()->make();
        $this->user->products()->save($product);

        $this->json('GET', '/api/single-product?id=', $product->id,)
            ->assertOk();
    }
<<<<<<< HEAD
}
=======

    public function test_product_can_be_deleted()
    {
        $product = Product::factory()->make();
        $this->user->products()->save($product);

        $this->json('DELETE', '/api/delete-product?id=' . $product->id)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Product Deleted'
            ]);
    }
}
>>>>>>> 40edd22be4752f0744821fcfc8aebfc547840780
