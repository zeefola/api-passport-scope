<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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
            'name' => (string)$this->name,
            'quantity' => (int)$this->quantity,
            'amount' => (float)$this->amount,
            'sold' => (bool) $this->sold,
            'active' => (bool) $this->active,
            'userId' => (string)$this->user_id,
            'posted' => Carbon::parse($this->created_at)->format('Y-m-d H:m:s'),
        ];
    }
}