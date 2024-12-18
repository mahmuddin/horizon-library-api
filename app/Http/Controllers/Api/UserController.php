<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    /**
     * Registers a new user and returns a JSON response containing the new user's info.
     *
     * @param UserRegisterRequest $request
     * @return JsonResponse
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        //set validation
        $data = $request->validated();

        if (User::where('username', $data['username'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'username' => ['The username has already been taken.']
                ]
            ], 400));
        }
        $user = new User(attributes: $data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Authenticates a user with the given credentials and returns a JSON response containing the user's info and a JWT token.
     *
     * @param UserLoginRequest $request
     * @return UserResource
     */
    public function login(UserLoginRequest $request): UserResource
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Username or password wrong.']
                ]
            ], 401));
        }

        $credentials = $request->only('username', 'password');
        $token = JWTAuth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']]);

        return new UserResource($user, $token);
    }

    public function logout(): JsonResponse
    {
        //remove token
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if ($removeToken) {
            return response()->json([
                'data' => true
            ], 200);
        } else {
            return response()->json([
                'data' => false
            ], 400);
        }
    }

    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = Auth::user();

        $fillableFields = ['name', 'email', 'username', 'password'];

        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'password') {
                    $user->{$field} = Hash::make($data[$field]);
                } else {
                    $user->{$field} = $data[$field];
                }
            }
        }

        $user->save();
        return new UserResource($user);
    }
}
