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
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
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
        if (!$token) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Could not create token.']
                ]
            ], 500));
        }

        // Generate refresh token (optional if you implement refresh logic)
        $refreshToken = JWTAuth::customClaims(['type' => 'refresh'])->fromUser($user);

        return new UserResource($user, $token, $refreshToken);
    }

    /**
     * Refreshes the JWT token for the given user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token has expired and cannot be refreshed',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Failed to refresh token. Please login again.',
            ], 401);
        }
    }

    /**
     * Logs out the authenticated user by invalidating their JWT token.
     *
     * @return JsonResponse
     */
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

    /**
     * Returns the authenticated user.
     *
     * @param Request $request
     * @return UserResource
     */
    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        $user->load('contacts.addresses');

        return new UserResource($user);
    }

    /**
     * Updates the authenticated user's information.
     *
     * @param UserUpdateRequest $request
     * @return UserResource
     */
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
