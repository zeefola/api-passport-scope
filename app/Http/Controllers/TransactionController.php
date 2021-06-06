<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\TransactionRepository;

class TransactionController extends Controller
{

    protected $transactionrepository;

    public function __construct($transactionrepository)
    {
        $this->transactionrepository = $transactionrepository;
    }

    public function initializeTransaction()
    {
    }

    public function markAsPaid()
    {
    }

    public function confirmPayment()
    {
    }

    public function cancelTransaction()
    {
    }

    public function getSingleTransaction()
    {
    }

    public function getAllTransaction()
    {
    }
}