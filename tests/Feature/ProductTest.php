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

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api'); //log user in through api
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
            'user_id' => Auth::id(),
        ];

        $this->json('POST', '/api/create-product', $data)
            ->assertOk()
            ->assertJson($data);
        // ->assertJson(['data' => $data]);
    }

    // public function test_required_inputs()
    // {
    //     // $this->withoutExceptionHandling();

    //     $response = $this->post('api/create-books', [
    //         'name' => '',
    //         'quantity' => '',
    //         'amount' => '',
    //     ]);

    //     $response->assertSessionHasErrors('name');
    // }

    public function test_can_show_product_by_id()
    {
        $product = Product::factory()->make();
        $this->user->products()->save($product);

        $this->json('GET', '/api/products', $product->id)
            ->assertOk();
    }
}
