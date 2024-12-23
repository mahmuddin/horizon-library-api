<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserCategoryCreateRequest;
use App\Http\Requests\UserCategoryUpdateRequest;
use App\Http\Resources\UserCategoryCollection;
use App\Http\Resources\UserCategoryResource;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserCategoryController extends Controller
{

    private function getUserCategory(int $idUserCategory): UserCategory
    {
        $idUserCategory = UserCategory::where('id', $idUserCategory)->first();
        if (!$idUserCategory) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ], 404));
        }
        return $idUserCategory;
    }

    /**
     * Retrieves a list of user categories.
     *
     * @return JsonResponse A JSON response containing the list of user categories in JSON format.
     */
    public function list(): JsonResponse
    {
        $user_category = UserCategory::all();

        return response()->json([
            'data' => UserCategoryResource::collection($user_category)
        ])->setStatusCode(200);
    }

    /**
     * Creates a new user category.
     *
     * @param UserCategoryCreateRequest $request
     * @return JsonResponse
     */
    public function create(UserCategoryCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user_category = new UserCategory($data);
        $user_category->save();
        return (new UserCategoryResource($user_category))->response()->setStatusCode(201);
    }

    /**
     * Searches for user categories based on query parameters
     * such as name and description. The results are paginated.
     *
     * @param Request $request The HTTP request instance containing query parameters.
     * @return UserCategoryCollection A collection of user categories that match the search criteria.
     */
    public function search(Request $request): UserCategoryCollection
    {
        $page = $request->query('page', 1);
        $size = $request->input('size', 10);
        $user_category = UserCategory::where(function (Builder $query) use ($request) {
            $name = $request->input('name');
            $description = $request->input('description');
            if ($name) {
                $query->where(function (Builder $query) use ($name) {
                    $query->orWhere('name', 'ilike', '%' . $name . '%');
                });
            }
            if ($description) {
                $query->where(function (Builder $query) use ($description) {
                    $query->orWhere('description', 'ilike', '%' . $description . '%');
                });
            }
        })
            ->paginate($size);
        return new UserCategoryCollection($user_category);
    }

    /**
     * Retrieves a user category by id.
     *
     * @param int $id The id of the user category.
     * @return UserCategoryResource
     */
    public function get(int $id): UserCategoryResource
    {
        $user_category = $this->getUserCategory($id);
        return new UserCategoryResource($user_category);
    }

    /**
     * Updates a user category.
     *
     * @param int $id The id of the user category.
     * @param UserCategoryUpdateRequest $request The request containing the updated user category data.
     * @return UserCategoryResource The updated user category in JSON format.
     */
    public function update(int $id, UserCategoryUpdateRequest $request): UserCategoryResource
    {
        $user_category = $this->getUserCategory($id);
        $data = $request->validated();
        $user_category->fill($data)->save();
        return (new UserCategoryResource($user_category));
    }

    public function delete(int $id): JsonResponse
    {
        $user_category = $this->getUserCategory($id);
        $user_category->delete();
        return response()->json(['data' => true], 200);
    }
}
