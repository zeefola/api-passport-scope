<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'email' => (string)$this->email,
            'scopes' => (array) json_decode($this->scopes),
            'joined' => Carbon::parse($this->created_at)->format('Y-m-d H:m:s'),
        ];
    }
}