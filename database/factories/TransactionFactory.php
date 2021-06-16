<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomDigit(),
            'product_id' => $this->faker->randomDigit(),
            'quantity' => $this->faker->numberBetween($min = 1, $max = 9000),
            'total_amount' => 10 * 50,
            'paid' => false,
            'confirmed' => false,
            'cancel' => false,
        ];
    }
}
