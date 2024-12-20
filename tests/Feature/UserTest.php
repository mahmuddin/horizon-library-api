<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class UserTest extends TestCase
{

    public function testRegisterSuccess()
    {
        $response = $this->post('/api/users/register', [
            'name' => 'Mahmuddin',
            'email' => 'mahmuddin@mail.com',
            'username' => 'mahmuddin',
            'password' => 'admin123',
            'password_confirmation' => 'admin123'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Mahmuddin',
                    'email' => 'mahmuddin@mail.com',
                    'username' => 'mahmuddin',
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $response = $this->post('/api/users/register', [
            'name' => '',
            'email' => '',
            'username' => '',
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => ['The name field is required.'],
                    "email" => ['The email field is required.'],
                    'username' => ['The username field is required.'],
                    "password" => ['The password field is required.'],
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        //Register User
        $this->testRegisterSuccess();
        // Register User with the same username
        $response = $this->post('/api/users/register', [
            'name' => 'Mahmuddin',
            'email' => 'mahmuddin@mail.com',
            'username' => 'mahmuddin',
            'password' => 'admin123',
            'password_confirmation' => 'admin123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => ['The username has already been taken.']
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);

        // Login
        $response = $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@mail.com',
                    'username' => 'test',
                ]
            ]);
    }

    public function testLoginFailedUsernameNotFound()
    {
        //Login with username not found
        $response = $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['Username or password is incorrect.']
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Login with wrong password
        $response = $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'salah',
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Username or password is incorrect.']
                ]
            ]);
    }
    public function testGetSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get User Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get User
        $response = $this->get('/api/users/current', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'name' => 'Test User',
                    'username' => 'test',
                    'email' => 'test@mail.com'
                ]
            ]);
    }

    public function testGetUnauthorized()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get User without Token
        $response = $this->get('/api/users/current');
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthenticated.']
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get User with Invalid Token
        $response = $this->get('/api/users/current', [
            'Authorization' => 'Bearer invalidtoken'
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthenticated.']
                ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Old User
        $oldUser = User::where('username', 'test')->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update User Name
        $response = $this->patch('/api/users/current', [
            'name' => 'baru',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'name' => 'baru',
                    'username' => 'test',
                    'email' => 'test@mail.com'
                ]
            ]);

        // Get New User
        $newUser = User::where('username', 'test')->first();
        // Compare Old User Name with New User Name
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Old User
        $oldUser = User::where('username', 'test')->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update User Password
        $response = $this->patch('/api/users/current', [
            'password' => 'baru123',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'name' => 'Test User',
                    'username' => 'test',
                    'email' => 'test@mail.com'
                ]
            ]);
        // Get New User
        $newUser = User::where('username', 'test')->first();
        // Compare Old User Password with New User Password
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdatePasswordFailed()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Old User
        $oldUser = User::where('username', 'test')->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update User Password
        $response = $this->patch('/api/users/current', [
            'password' => '123',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'password' => ['The password field must be at least 5 characters.'],
                ]
            ]);
    }

    public function testUpdateFailed()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update User with Long Name
        $response = $this->patch('/api/users/current', [
            'name' => Str::random(101),
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'name' => ['The name field must not be greater than 100 characters.']
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Logout
        $response = $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    public function testLogoutFailed()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        $response = $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'salah'
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthenticated.']
                ]
            ]);
    }
}
