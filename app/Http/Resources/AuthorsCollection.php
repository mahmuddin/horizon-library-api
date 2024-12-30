<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AuthorsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'data' => AuthorResource::collection($this->resource)
            ];
        } else {
            return [];
        }
    }
}
