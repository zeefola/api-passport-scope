<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Product;
use App\Repository\ProductRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
//    use RefreshDatabase;

public function test_product_name_already_exist(){
    $product_name = (new ProductRepository()->createProduct(...));
    $this->assertEquals(expected:0, $product_name);
}

}
