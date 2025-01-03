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
        $userCategory = UserCategory::where('id', $idUserCategory)->first();
        if (!$userCategory) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ], 404));
        }
        return $userCategory;
    }

    /**
     * Retrieves a list of user categories.
     *
     * @return JsonResponse A JSON response containing the list of user categories in JSON format.
     */
    public function list(): JsonResponse
    {
        $user_categories = UserCategory::all();

        return response()->json([
            'data' => UserCategoryResource::collection($user_categories)
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
        $user_categories = new UserCategory($data);
        $user_categories->save();
        return (new UserCategoryResource($user_categories))->response()->setStatusCode(201);
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
        $user_categories = UserCategory::where(function (Builder $query) use ($request) {
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
        return new UserCategoryCollection($user_categories);
    }

    /**
     * Retrieves a user category by id.
     *
     * @param int $id The id of the user category.
     * @return UserCategoryResource
     */
    public function get(int $id): UserCategoryResource
    {
        $user_categories = $this->getUserCategory($id);
        return new UserCategoryResource($user_categories);
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
        $user_categories = $this->getUserCategory($id);
        $data = $request->validated();
        $user_categories->fill($data)->save();
        return (new UserCategoryResource($user_categories));
    }

    public function delete(int $id): JsonResponse
    {
        $user_categories = $this->getUserCategory($id);
        $user_categories->delete();
        return response()->json(['data' => true], 200);
    }
}
