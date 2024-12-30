<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorCreateRequest;
use App\Http\Requests\AuthorUpdateRequest;
use App\Http\Resources\AuthorsCollection;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    /**
     * Find an author by its ID.
     *
     * @param int $idAuthor
     * @return Author
     * @throws HttpResponseException if the author is not found
     */
    public function getAuthor(int $idAuthor): Author
    {
        // Find Auhtor
        $author = Author::whereId($idAuthor)->first();
        // If the author is not found, return a 404 response
        if (!$author) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ], 404));
        }
        return $author;
    }

    /**
     * Creates a new author and returns a JSON response with the author's information.
     *
     * @param AuthorCreateRequest $request The request object containing the data to create an author.
     * @return JsonResponse A JSON response containing the newly created author's information.
     */
    public function create(AuthorCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Proses gambar (jika ada)
        if ($request->hasFile('profile_image')) {
            // Simpan gambar ke storage/images
            $imagePath = $request->file('profile_image')->store('author_images', 'public');
            // Tambahkan path gambar ke dalam data
            $data['profile_image'] = $imagePath;
        }
        $author = new Author($data);
        $author->save();
        return (new AuthorResource($author))->response()->setStatusCode(201);
    }

    /**
     * Retrieves a list of all authors.
     *
     * @return JsonResponse A JSON response containing the list of authors in JSON format.
     */
    public function list(): JsonResponse
    {
        $authors = Author::get();

        return response()->json([
            'data' => AuthorResource::collection($authors)
        ], 200);
    }

    /**
     * Searches for authors based on query parameters such as name and description.
     * The results are paginated.
     *
     * @param Request $request The HTTP request instance containing query parameters.
     * @return AuthorsCollection A collection of authors that match the search criteria.
     */
    public function search(Request $request): AuthorsCollection
    {
        $page = $request->query('page', 1);
        $size = $request->input('size', 10);
        $authors = Author::where(function (Builder $query) use ($request) {
            $name = $request->input('name');
            $address = $request->input('address');
            $phone = $request->input('phone');
            $email = $request->input('email');
            if ($name) {
                $query->where(function (Builder $query) use ($name) {
                    $query->orWhere('name', 'ilike', '%' . $name . '%');
                });
            }
            if ($address) {
                $query->where(function (Builder $query) use ($address) {
                    $query->orWhere('address', 'ilike', '%' . $address . '%');
                });
            }
            if ($phone) {
                $query->where(function (Builder $query) use ($phone) {
                    $query->orWhere('phone', 'ilike', '%' . $phone . '%');
                });
            }
            if ($email) {
                $query->where(function (Builder $query) use ($email) {
                    $query->orWhere('email', 'ilike', '%' . $email . '%');
                });
            }
        })
            ->paginate(perPage: $size);
        return new AuthorsCollection($authors);
    }

    /**
     * Retrieves an author by its ID.
     *
     * @param int $id The ID of the author to retrieve.
     * @return AuthorResource The author resource associated with the given ID.
     * @throws HttpResponseException If the author is not found, throws an exception with a 404 response.
     */
    public function get(int $id): AuthorResource
    {
        $author = $this->getAuthor($id);
        return new AuthorResource($author);
    }

    /**
     * Updates an author.
     *
     * @param int $id The ID of the author to update.
     * @param AuthorUpdateRequest $request The request containing the updated author data.
     * @return AuthorResource The updated author in JSON format.
     * @throws HttpResponseException If the author is not found, throws an exception with a 404 response.
     */
    public function update(int $id, AuthorUpdateRequest $request): AuthorResource
    {
        $author = $this->getAuthor($id);
        $data = $request->validated();

        // Proses unggahan file (jika ada)
        if ($request->hasFile('profile_image')) {
            // Hapus file lama jika ada
            if ($author->profile_image) {
                Storage::disk('public')->delete($author->profile_image);
            }

            // Simpan file baru ke disk 'public'
            $profileImagePath = $request->file('profile_image')->store('auhtor_images', 'public');

            // Tambahkan path baru ke dalam data
            $data['profile_image'] = $profileImagePath;
        }

        $author->fill($data);
        $author->save();
        return new AuthorResource($author);
    }

    /**
     * Deletes an author.
     *
     * @param int $id The ID of the author to delete.
     * @return JsonResponse The response containing a boolean indicating whether the deletion was successful.
     * @throws HttpResponseException If the author is not found, throws an exception with a 404 response.
     */
    public function delete(int $id): JsonResponse
    {
        $author = $this->getAuthor($id);
        // Hapus file profile_image jika ada
        if ($author->profile_image) {
            Storage::disk('public')->delete($author->profile_image);
        }
        // Hapus kontak dari database
        $author->delete();
        return response()->json(['data' => true], 200);
    }
}
