<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\ProductRepository;

class ProductController extends Controller
{

    protected $productrepository;

    public function __construct($productrepository)
    {
        $this->productrepository = $productrepository;
    }

    public function createProduct()
    {
    }

    public function getAllProduct()
    {
    }

    public function getSingleProduct()
    {
    }

    public function updateProduct()
    {
    }

    public function deleteProduct()
    {
    }

    public function restockProduct()
    {
    }

    public function markAsSold()
    {
    }
}