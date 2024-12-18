<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private $token;

    public function __construct($resource, $token = null)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (isset($this->token)) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'token' => $this->token
            ];
        } else {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
            ];
        }
    }
}
