<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Product;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** Test Produts required fields
     * @return void
     */

    public function test_products_required_inputs()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api')->json('POST', '/api/create-product')
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

    /** Test unauthenticated user cannot create product
     * @return void
     */

    public function test_unauthenticated_user_cannot_create_product()
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
        $this->withoutExceptionHandling(); //display real error

        $data = [
            'name' => 'Mango Juice',
            'quantity' => 90,
            'amount' => 150,
            'sold' => false,
            'active' => true,
        ];
        $user = User::factory()->create();

        $response =  $this->actingAs($user, 'api')
            ->json('POST', '/api/create-product', $data)
            ->assertOk();

        $response->assertJson([
            'error' => false,
            'msg' => 'Product Created Successfully'
        ]);

        $this->assertDatabaseHas('products', $data);
    }

    public function test_product_name_already_exist()
    {

        Product::factory()->create();

        $product = [
            'name' => 'Mango Juice',
            'quantity' => 50,
            'amount' => 950,
            'sold' => false,
            'active' => true,
            'user_id' => 1,
        ];

        $user = User::factory()->create();

        $response =  $this->actingAs($user, 'api')->json('POST', '/api/create-product', $product);

        $response->assertJson([
            'error' => true,
            'msg' => 'Product Name Already exists'
        ]);
    }

    public function test_a_product_can_be_updated()
    {
        $this->withoutExceptionHandling(); //display real error

        $product = Product::factory()->create();

        $user = User::factory()->create();

        $quantity = 30;
        $updatedData = [
            'quantity' => $product['quantity'] + $quantity,
        ];

        $response = $this->actingAs($user, 'api')
            ->json('PUT', '/api/update-product?id=' . $product->id, $updatedData)
            ->assertOk();

        $response->assertJson([
            'error' => false,
            'msg' => 'Product Updated'
        ]);

        $this->assertDatabaseHas('products', [
            'quantity' => $updatedData,
            'name' => $product->name
        ]);
    }

    public function test_getting_all_products()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create();
        $response = $this->json('GET', '/api/products')
            ->assertStatus(200);

        $response->assertJson([
            'data' => array(
                [
                    'id' => $product->id,
                    'quantity' => $product->quantity,
                    'amount' => $product->amount,
                    'sold' => $product->sold,
                    'active' => $product->active,
                    'userId' => $product->user_id,
                ]
            )
        ]);
    }

    public function test_can_show_product_by_id()
    {
        $product = Product::factory()->create();

        $response = $this->json('GET', '/api/single-product?id=' . $product->id)
            ->assertOk();
        $response->assertJson([
            'name' => $product->name
        ]);
    }

    public function test_product_can_be_deleted()
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', '/api/delete-product?id=' . $product->id)
            ->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'msg' => 'Product Deleted'
        ]);
    }

    public function test_product_id_doesnt_exist()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', '/api/delete-product?id=5')
            ->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'msg' => 'Product Not Found'
        ]);
    }

    public function test_a_product_can_be_mark_as_restock()
    {
        $this->withoutExceptionHandling(); //display real error
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/restock-product?id=' . $product->id, [
                'quantity' => 20,
                'sold' => false,
                'active' => true
            ])
            ->assertOk()
            ->assertJson([
                'error' => false,
                'msg' => 'Product Restocked',
                'data' => [
                    'name' => 'Mango Juice',
                    'quantity' => 70,
                    'sold' => false,
                    'active' => true
                ]
            ]);
    }

    public function test_a_product_can_be_mark_as_sold()
    {
        $this->withoutExceptionHandling(); //display real error

        $product = Product::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->json('PUT', '/api/mark-as-sold?id=' . $product->id, [
                'quantity' => 0,
                'sold' => true,
                'active' => false
            ])
            ->assertOk()
            ->assertJson([
                'error' => false,
                'msg' => 'Marked as Sold',
                'data' => [
                    'name' => 'Mango Juice',
                    'quantity' => 0,
                    'sold' => true,
                    'active' => false
                ]
            ]);
    }
}