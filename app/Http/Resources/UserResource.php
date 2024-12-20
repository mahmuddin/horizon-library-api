<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private $token, $refreshToken;

    /**
     * Create a new UserResource instance.
     *
     * @param mixed $resource The resource being transformed.
     * @param string|null $token The access token, if available.
     * @param string|null $refreshToken The refresh token, if available.
     */

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
        if (isset($this->token)) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'access_token' => $this->token,
                'refresh_token' => $this->refreshToken,
            ];
        } else {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'relationships' => [
                    'contacts' => $this->contacts->map(function ($contact) {
                        return [
                            'id' => $contact->id,
                            'first_name' => $contact->first_name,
                            'last_name' => $contact->last_name,
                            'email' => $contact->email,
                            'phone' => $contact->phone,
                            'addresses' => $contact->addresses,
                        ];
                    }),
                ],
            ];
        }
    }
}
