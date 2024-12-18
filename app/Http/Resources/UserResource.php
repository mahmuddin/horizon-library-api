<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private $token, $refreshToken;

    public function __construct($resource, $token = null, $refreshToken = null)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->refreshToken = $refreshToken;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (isset($this->token) && isset($this->refreshToken)) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->username,
                'token' => $this->token,
                'refresh_token' => $this->refreshToken
            ];
        } else {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->username,
            ];
        }
    }
}
