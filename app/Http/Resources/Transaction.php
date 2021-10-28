<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => (string)$this->id,
            'quantity' => (int)$this->quantity,
            'total_amount' => (float)$this->total_amount,
            'paid' => (bool) $this->paid,
            'confirmed' => (bool) $this->confirmed,
            'cancel' => (bool) $this->cancel,
            'userId' => (string)$this->user_id,
            'productId' => (string)$this->product_id,
            'posted' => Carbon::parse($this->created_at)->format('Y-m-d H:m:s'),
        ];
    }
}