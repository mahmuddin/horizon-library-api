<?php

namespace Tests\Feature;

use App\Models\UserCategory;
use Database\Seeders\SearchUserCategorySeeder;
use Database\Seeders\UserCategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserCategoryTest extends TestCase
{
    /**
     * Test that a user category can be created successfully.
     *
     * This test seeds the database with users, logs in a user, creates a user category,
     * and checks that the response is 201 and that the user category is returned with
     * the correct fields.
     */
    public function testCreateSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        //Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->post('/api/user_category', [
            'name' => 'admin',
            'description' => 'admin'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(201)->assertJson([
            'data' => [
                'name' => 'admin',
                'description' => 'admin'
            ]
        ]);
    }

    /**
     * Test that creating a user category fails when required fields are empty.
     *
     * This test seeds the database with users, logs in a user, creates a user category
     * with empty required fields, and checks that the response is 400 and contains the
     * appropriate validation error messages.
     */
    public function testCreateFailed()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        //Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->post('/api/user_category', [
            'name' => '',
            'description' => ''
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(400)->assertJson([
            'errors' => [
                'name' => ['The name field is required.'],
                'description' => ['The description field is required.']
            ]
        ]);
    }

    /**
     * Test that creating a user category fails when the user is not authenticated.
     *
     * This test seeds the database with users, attempts to create a user category
     * without providing a JWT token, and checks that the response is 401
     * with the appropriate authentication error message.
     */
    public function testCreateUnauthenticated()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        //Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->post('/api/user_category', [
            'name' => 'admin',
            'description' => 'admin'
        ], []);
        $response->assertStatus(401)->assertJson([
            'errors' => [
                "message" => ['Unauthenticated.']
            ]
        ]);
    }

    /**
     * Test that a user category can be retrieved successfully.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * retrieves a user category by its ID, and checks that the response is 200
     * with the correct user category details in the response JSON.
     */
    public function testGetSuccess()
    {
        // Running the UserSeeder
        $this->seed([
            UserSeeder::class,
            UserCategorySeeder::class
        ]);
        // Get User Category
        $user_category = UserCategory::query()->limit(1)->first();
        //Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/user_category/' . $user_category->id, [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)->assertJson([
            'data' => [
                'name' => 'Superadmin',
                'description' => 'Fungsi: Tingkatan admin tertinggi, biasanya digunakan untuk organisasi besar.'
            ]
        ]);
    }

    /**
     * Test that retrieving a non-existent user category fails.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * retrieves a non-existent user category by its ID, and checks that the response
     * is 404 with the appropriate error message in the response JSON.
     */
    public function testGetNotFound()
    {
        // Running the UserSeeder
        $this->seed([
            UserSeeder::class,
            UserCategorySeeder::class
        ]);
        // Get User Category
        $user_category = UserCategory::query()->limit(1)->first();
        //Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/user_category/' . $user_category->id + 100, [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found.']
            ]
        ]);
    }

    /**
     * Tests that the user category list endpoint returns a successful response with the correct user category details.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * retrieves the list of user categories, and checks that the response status is 200.
     * It verifies that the response JSON contains the expected user category details.
     */
    public function testListSuccess()
    {
        // Running the UserSeeder
        $this->seed([
            UserSeeder::class,
            UserCategorySeeder::class
        ]);
        //Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/user_category', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'name' => 'Superadmin',
                    'description' => 'Fungsi: Tingkatan admin tertinggi, biasanya digunakan untuk organisasi besar.',
                ],
                [
                    'name' => 'Administrator',
                    'description' => 'Fungsi: Mengelola seluruh sistem e-library, termasuk pengguna, koleksi digital, dan pengaturan aplikasi.',
                ],
                [
                    'name' => 'Pustakawan',
                    'description' => 'Fungsi: Mengelola koleksi dan membantu pengguna dalam mengakses informasi.',
                ],
                [
                    'name' => 'Anggota',
                    'description' => 'Fungsi: Pengguna utama yang mengakses konten perpustakaan.',
                ],
                [
                    'name' => 'Pengunjung Umum',
                    'description' => 'Pengguna yang tidak memiliki akun atau belum login ke sistem.',
                ],
                [
                    'name' => 'Contributor',
                    'description' => 'Pengguna yang bertanggung jawab menambahkan koleksi konten baru, seperti penulis atau penerbit',
                ]
            ]
        ]);
    }

    /**
     * Test that a user category can be updated successfully.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * updates a user category by its ID, and checks that the response is 200
     * with the updated user category details in the response JSON.
     */
    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->put(
            "/api/user_category/{$user_category->id}",
            [
                'name' => 'update',
                'description' => 'update',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'name' => 'update',
                    'description' => 'update',
                ]
            ]);
    }

    /**
     * Test that updating a user category fails when the required fields are empty.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * updates a user category with empty required fields, and checks that the response
     * is 400 with the appropriate validation error messages.
     */
    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->put(
            "/api/user_category/{$user_category->id}",
            [
                'name' => '',
                'description' => '',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => ["The name field is required."],
                    "description" => ["The description field is required."],
                ]
            ]);
    }

    /**
     * Test that deleting a user category returns a success response.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * deletes a user category, and checks that the response is 200 with a JSON
     * response indicating success.
     */
    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->delete(
            "/api/user_category/{$user_category->id}",
            [],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }

    /**
     * Test that deleting a non-existent user category returns a 404 not found response.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * attempts to delete a user category with an ID that does not exist, and checks
     * that the response is 404 with the appropriate error message in the response JSON.
     */
    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->delete(
            '/api/user_category/' . ($user_category->id + 100),
            [],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found.']
            ]
        ]);
    }

    /**
     * Tests that searching for a user category by name returns a success response with the correct user category details.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * searches for a user category by name, and checks that the response is 200
     * with a JSON response containing the expected user category details.
     */
    public function testSearchByName()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get(
            '/api/user_category/search?name=' . $user_category->name,
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => $user_category->id,
                    'name' => $user_category->name,
                    'description' => $user_category->description,
                ]
            ]
        ]);
    }

    /**
     * Tests that searching for a user category by description returns a success response with the correct user category details.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * searches for a user category by description, and checks that the response is 200
     * with a JSON response containing the expected user category details.
     */
    public function testSearchByDescription()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get(
            '/api/user_category/search?description=' . $user_category->description,
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => $user_category->id,
                    'name' => $user_category->name,
                    'description' => $user_category->description,
                ]
            ]
        ]);
    }

    /**
     * Tests that searching for a user category that does not exist returns a success response with an empty JSON response.
     *
     * This test seeds the database with users and user categories, logs in a user,
     * searches for a user category that does not exist, and checks that the response is 200
     * with a JSON response containing an empty array.
     */
    public function testSearchNotFound()
    {
        $this->seed([UserSeeder::class, UserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get(
            '/api/user_category/search?name=0tidak ada',
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200)->assertJson([
            'data' => []
        ]);
    }

    public function testSearchWithPage()
    {
        $this->seed([UserSeeder::class, SearchUserCategorySeeder::class]);
        $user_category = UserCategory::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get(
            '/api/user_category/search?size=5&page=2',
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );
        $response->assertStatus(200);

        // Log and verify response structure
        $responseData = $response->json();
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(5, count($responseData['data']));
        self::assertEquals(20, $responseData['meta']['total']);
        self::assertEquals(2, $responseData['meta']['current_page']);
    }
}
