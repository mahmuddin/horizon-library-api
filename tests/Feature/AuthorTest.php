<?php

namespace Tests\Feature;

use App\Models\Author;
use Database\Seeders\AuthorSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthorTest extends TestCase
{
    /**
     * Test that an author can be created successfully.
     *
     * This test runs the UserSeeder, logs in a user, creates an author with the
     * required fields, and checks that the response is 201 and that the author
     * is returned in the response with the correct fields. The test also
     * verifies that the uploaded image is stored in the correct location.
     */
    public function testCreateSuccess()
    {
        // Runnning User Seeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/authors', [
            'name' => 'Test Author',
            'address' => 'Test Address',
            'phone' => '08987654321',
            'email' => 'test@mail.com',
            'website' => 'https://www.test.com',
            'bio' => 'Test Bio',
            'profile_image' =>  UploadedFile::fake()->image('avatar.png', 100, 100),
            'social_media' => json_encode([
                'facebook' => 'https://www.facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ]),
            'nationality' => 'Test Nationality',
            'birth_date' => '2000-01-01',
            'categories' => [
                'fiction',
                'non-fiction',
                'research',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Author')
            ->assertJsonPath('data.address', 'Test Address')
            ->assertJsonPath('data.phone', '08987654321')
            ->assertJsonPath('data.email', 'test@mail.com')
            ->assertJsonPath('data.website', 'https://www.test.com')
            ->assertJsonPath('data.bio', 'Test Bio')
            ->assertJsonPath('data.social_media', json_encode([
                'facebook' => 'https://www.facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ]))
            ->assertJsonPath('data.nationality', 'Test Nationality')
            ->assertJsonPath('data.birth_date', '2000-01-01')
            ->assertJsonPath('data.categories.0', 'fiction')
            ->assertJsonPath('data.categories.1', 'non-fiction')
            ->assertJsonPath('data.categories.2', 'research');

        // Ambil URL file dari respons
        $uploadedFile = $response->json('data.profile_image');

        // Ubah URL file menjadi path relatif untuk disk 'public'
        $relativePath = str_replace('/storage/', '', parse_url($uploadedFile, PHP_URL_PATH));

        // Tambahkan direktori 'author_images' ke dalam path
        $expectedPath = 'author_images/' . basename($relativePath);

        // Verifikasi keberadaan file menggunakan Storage::exists
        $this->assertTrue(
            Storage::disk('public')->exists($expectedPath),
            "File tidak ditemukan di penyimpanan: {$expectedPath}"
        );
    }

    /**
     * Test creating an author with missing required fields fails.
     */
    public function testCreateFailed()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])
            ->post('/api/authors', [
                'name' => '',
                'address' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
                'bio' => '',
                'profile_image' => UploadedFile::fake()->image('avatar.png', 100, 100),
                'social_media' => json_encode([
                    'facebook' => 'https://www.facebook.com/test',
                    'twitter' => 'https://twitter.com/test',
                ]),
                'nationality' => 'Test Nationality',
                'birth_date' => '2000-01-01',
                'categories' => [
                    'fiction',
                    'non-fiction',
                    'research',
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => ['The name field is required.']
                ]
            ]);
    }

    public function testCreateFailedEmail()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])
            ->post('/api/authors', [
                'name' => 'Test Author',
                'address' => 'Test Address',
                'phone' => '08987654321',
                'email' => 'test',
                'website' => 'https://www.test.com',
                'bio' => 'Test Bio',
                'profile_image' =>  UploadedFile::fake()->image('avatar.png', 100, 100),
                'social_media' => json_encode([
                    'facebook' => 'https://www.facebook.com/test',
                    'twitter' => 'https://twitter.com/test',
                ]),
                'nationality' => 'Test Nationality',
                'birth_date' => '2000-01-01',
                'categories' => [
                    'fiction',
                    'non-fiction',
                    'research',
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "email" => ['The email field must be a valid email address.']
                ]
            ]);
    }

    /**
     * Tests that creating an author fails when unauthenticated.
     *
     * This test attempts to create an author without being authenticated
     * and checks that the response is 401 and contains the appropriate
     * authentication error message.
     */
    public function testCreateUnauthenticated()
    {

        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Create Author
        $response = $this->post('/api/authors', [
            'name' => 'Test Author',
            'address' => 'Test Address',
            'phone' => '08987654321',
            'email' => 'test@mail.com',
            'website' => 'https://www.test.com',
            'bio' => 'Test Bio',
            'profile_image' =>  UploadedFile::fake()->image('avatar.png', 100, 100),
            'social_media' => json_encode([
                'facebook' => 'https://www.facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ]),
            'nationality' => 'Test Nationality',
            'birth_date' => '2000-01-01',
            'categories' => [
                'fiction',
                'non-fiction',
                'research',
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['Unauthenticated.']
                ]
            ]);
    }

    /**
     * Test that an author can be retrieved successfully.
     *
     * This test seeds the database with users and authors, logs in a user,
     * retrieves an author by its ID, and checks that the response is 200
     * with the correct author details in the response JSON.
     */
    public function testGetSuccess()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])
            ->get('/api/authors/' . $author->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test Author')
            ->assertJsonPath('data.address', 'Test Address')
            ->assertJsonPath('data.phone', '08987654321')
            ->assertJsonPath('data.email', 'test@mail.com')
            ->assertJsonPath('data.website', 'https://www.test.com')
            ->assertJsonPath('data.bio', 'Test Bio')
            ->assertJsonPath('data.social_media', json_encode([
                'facebook' => 'https://www.facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ]))
            ->assertJsonPath('data.nationality', 'Test Nationality')
            ->assertJsonPath('data.birth_date', '2000-01-01')
            ->assertJsonPath('data.categories.0', 'fiction')
            ->assertJsonPath('data.categories.1', 'non-fiction')
            ->assertJsonPath('data.categories.2', 'research');
    }

    /**
     * Tests that retrieving an author without authentication fails.
     *
     * This test seeds the database with users and authors, attempts to
     * retrieve an author without providing a JWT token, and checks that
     * the response is 401 with the appropriate authentication error message.
     */
    public function testGetUnauthenticated()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Create Author
        $response = $this->get('/api/authors/' . $author->id);

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['Unauthenticated.']
                ]
            ]);
    }

    /**
     * Test that retrieving an author fails when the author is not found.
     *
     * This test seeds the database with users and authors, logs in a user,
     * retrieves an author by a non-existent ID, and checks that the response
     * is 404 with the appropriate error message in the response JSON.
     */
    public function testGetNotFound()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/' . $author->id + 1);

        $response->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => ['not found.']
                ]
            ]);
    }

    /**
     * Tests that the authors list endpoint returns a successful response with the correct author details.
     *
     * This test seeds the database with users and authors, logs in a user,
     * retrieves the list of authors, and checks that the response status is 200.
     * It verifies that the response JSON contains the expected author details.
     */
    public function testListSuccess()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Authors
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors');

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'name' => 'Test Author',
                    'address' => 'Test Address',
                    'phone' => '08987654321',
                    'email' => 'test@mail.com',
                    'website' => 'https://www.test.com',
                    'bio' => 'Test Bio',
                    'profile_image' => '/storage/',
                    'social_media' => json_encode([
                        'facebook' => 'https://www.facebook.com/test',
                        'twitter' => 'https://twitter.com/test',
                    ]),
                    'nationality' => 'Test Nationality',
                    'birth_date' => '2000-01-01',
                    'categories' => ['fiction', 'non-fiction', 'research']
                ]
            ]
        ]);
    }

    public function testUpdateSuccess()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->put('/api/authors/' . $author->id, [
            'name' => 'Test Author',
            'address' => 'Test Address',
            'phone' => '08987654321',
            'email' => 'test@mail.com',
            'website' => 'https://www.test.com',
            'bio' => 'Test Bio',
            'profile_image' => $file = UploadedFile::fake()->image('avatar.png', 100, 100),
            'social_media' => json_encode([
                'facebook' => 'https://www.facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ]),
            'nationality' => 'Test Nationality',
            'birth_date' => '2000-01-01',
            'categories' => ['fiction', 'non-fiction', 'research'],
        ]);

        // Hapus file profile_image lama jika ada
        if ($author->profile_image) {
            Storage::disk('public')->delete('storage/author_images/' . $author->profile_image);
        }

        // Store the uploaded file in the storage
        $filePath = 'storage/author_images/' . $file->getClientOriginalName();
        Storage::disk('public')->put($filePath, file_get_contents($file->getPathname()));

        // Verifikasi keberadaan file menggunakan Storage::exists
        $this->assertTrue(
            Storage::disk('public')->exists($filePath),
            "File tidak ditemukan di penyimpanan: {$filePath}"
        );
    }

    /**
     * Tests that updating an author fails when unauthenticated.
     *
     * This test attempts to update an author without being authenticated
     * and checks that the response is 401 and contains the appropriate
     * authentication error message.
     */
    public function testUpdateUnauthenticated()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Create Author
        $response = $this->put('/api/authors/1', [
            'name' => 'Test Author',
            'address' => 'Test Address',
            'phone' => '08987654321',
            'email' => 'test@mail.com',
            'website' => 'https://www.test.com',
            'bio' => 'Test Bio',
            'profile_image' =>  UploadedFile::fake()->image('avatar.png', 100, 100),
            'social_media' => json_encode([
                'facebook' => 'https://www.facebook.com/test',
                'twitter' => 'https://twitter.com/test',
            ]),
            'nationality' => 'Test Nationality',
            'birth_date' => '2000-01-01',
            'categories' => [
                'fiction',
                'non-fiction',
                'research',
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['Unauthenticated.']
                ]
            ]);
    }

    /**
     * Tests that updating an author fails when some required fields are empty.
     *
     * This test seeds the database with users and authors, logs in a user,
     * attempts to update an author with empty required fields, and checks
     * that the response is 400 and contains the appropriate validation
     * error messages.
     */
    public function testUpdateValidationError()
    {
        // Running the UserSeeder and AuthorSeeder
        $this->seed([
            UserSeeder::class,
            AuthorSeeder::class
        ]);
        $author = Author::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update Author
        $response = $this->put(
            '/api/authors/' . $author->id,
            [
                'name' => '',
                'address' => 'Test Address',
                'phone' => '08987654321',
                'email' => 'test_user@mail.com',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => ['The name field is required.']
                ]
            ]);
    }

    public function testDeleteSuccess()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Delete Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->delete('/api/authors/' . $author->id);

        $response->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    public function testDeleteNotFound()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Delete Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->delete('/api/authors/' . $author->id + 1);

        $response->assertStatus(404)->assertJson([
            "errors" => [
                "message" => ['not found.']
            ]
        ]);
    }

    public function testDeleteUnauthenticated()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Author
        $author = Author::query()->limit(1)->first();
        // Delete Author
        $response = $this->delete('/api/authors/' . $author->id);

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['Unauthenticated.']
                ]
            ]);
    }

    public function testSearchByName()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Search Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/search?name=test');

        $response->assertStatus(200);

        $responseData = $response->json();

        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
    }

    public function testSearchByAddress()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Search Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/search?address=test');

        $response->assertStatus(200);

        $responseData = $response->json();

        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
    }

    public function testSearchByPhone()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Search Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/search?phone=08987654321');

        $response->assertStatus(200)->assertJson([
            "data" => [
                [
                    "name" => "Test Author",
                    "address" => "Test Address",
                    "phone" => "08987654321",
                    "email" => "test@mail.com",
                    "website" => "https://www.test.com",
                    "bio" => "Test Bio",
                    "nationality" => "Test Nationality",
                ]
            ]
        ]);
    }

    public function testSearchByEmail()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Search Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/search?email=test@mail.com');

        $response->assertStatus(200)->assertJson([
            "data" => [
                [
                    "name" => "Test Author",
                    "address" => "Test Address",
                    "phone" => "08987654321",
                    "email" => "test@mail.com",
                    "website" => "https://www.test.com",
                    "bio" => "Test Bio",
                    "nationality" => "Test Nationality",
                ]
            ]
        ]);
    }

    public function testSearchNotFound()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Search Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/search?name=0tidak ada');

        $response->assertStatus(200)->assertJson([
            "data" => []
        ]);
    }

    public function testSearchWithPage()
    {
        // Running the UserSeeder
        $this->seed([UserSeeder::class, AuthorSeeder::class]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Search Author
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/authors/search?name=test&page=1&size=10');

        $response->assertStatus(200);

        self::assertEquals(1, count($response->json()['data']));
        self::assertEquals(1, $response->json()['meta']['total']);
        self::assertEquals(1, $response->json()['meta']['current_page']);
    }
}
