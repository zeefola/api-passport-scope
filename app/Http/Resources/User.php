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
            'username' => (string)$this->username,
            'phoneNumber' => (string)$this->phone_number,
            'scopes' => (array) json_decode($this->scopes),
            'active' => (bool)$this->active,
            'last_login' => Carbon::parse($this->last_login)->format('Y-m-d H:m:s'),
            'joined' => Carbon::parse($this->created_at)->format('Y-m-d H:m:s'),
        ];
    }
}