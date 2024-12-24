<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ContactTest extends TestCase
{
    /**
     * Tests that a contact can be created successfully.
     *
     * This test logs in a user and then creates a contact with the
     * required fields. It checks that the response is 201 and that the
     * contact is returned in the response with the correct fields.
     */
    public function testCreateSuccess()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Contact
        $response = $this->post(
            '/api/contacts',
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '1234567890',
                'email' => 'test_user@mail',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(201)->assertJson([
            'data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '1234567890',
                'email' => 'test_user@mail',
            ]
        ]);
    }

    /**
     * Tests that creating a contact fails when required fields are invalid.
     *
     * This test logs in a user and attempts to create a contact with
     * missing and invalid fields. It checks that the response is 400
     * and contains the appropriate validation error messages.
     */

    public function testCreateFailed()
    {
        // Running the UserSeeder
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Create Contact
        $response = $this->post(
            '/api/contacts',
            [
                'first_name' => '',
                'last_name' => 'User',
                'phone' => '1234567890',
                'email' => 'test_user@mail,com',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "first_name" => ['The first name field is required.'],
                    "email" => ['The email field must be a valid email address.'],
                ]
            ]);
    }

    /**
     * Tests that creating a contact fails when unauthenticated.
     *
     * This test attempts to create a contact without being authenticated and
     * checks that the response is 401 and contains the appropriate
     * authentication error message.
     */
    public function testCreateUnauthenticated()
    {

        // Running the UserSeeder'
        $this->seed(UserSeeder::class);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        //Create Contact
        $response = $this->post('api/contacts', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '1234567890',
            'email' => 'test_user@mail,com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['Unauthenticated.']
                ]
            ]);
    }

    /**
     * Tests that a contact can be retrieved successfully.
     *
     * This test seeds the database with users and contacts, gets a contact
     * by its ID for an authenticated user, and checks that the response is
     * 200 with the correct contact details in the response JSON.
     */
    public function testGetSuccess()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/' . $contact->id, [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'phone' => '08987654321',
                    'email' => 'test_user@mail.com',
                ]
            ]);
    }


    /**
     * Tests that retrieving a contact that doesn't exist fails.
     *
     * This test seeds the database with users and contacts, attempts to
     * retrieve a contact that doesn't exist, and checks that the response is
     * 404 with the correct error message in the response JSON.
     */
    public function testGetNotFound()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        // Get Contact
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/' . ($contact->id + 1), [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    /**
     * Tests that attempting to retrieve a contact belonging to another user
     * returns a 404 not found response.
     *
     * This test logs in a user and attempts to access a contact with an ID
     * that does not belong to the authenticated user. It checks that the
     * response status is 404 and contains the appropriate error message.
     */
    public function testGetOtherUserContact()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        // Get Contact
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/' . $contact->id + 1, [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    /**
     * Tests that the contact list endpoint returns a successful response with the correct contact details.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * retrieves the list of contacts, and checks that the response status is 200.
     * It verifies that the response JSON contains the expected contact details.
     */
    public function testListSuccess()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        'first_name' => 'Test',
                        'last_name' => 'User',
                        'phone' => '08987654321',
                        'email' => 'test_user@mail.com',
                    ]
                ]
            ]);
    }

    /**
     * Tests that updating a contact successfully returns the updated contact
     * with the correct fields in the response JSON.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * updates a contact with the required fields, and checks that the response
     * status is 200 with the correct contact details in the response JSON.
     */
    public function testUpdateSuccess()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update Contact
        $response = $this->put(
            '/api/contacts/' . $contact->id,
            [
                'first_name' => 'Test Updated',
                'last_name' => 'User Updated',
                'phone' => '1234567891',
                'email' => 'test_user_update@mail.com',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'first_name' => 'Test Updated',
                    'last_name' => 'User Updated',
                    'phone' => '1234567891',
                    'email' => 'test_user_update@mail.com',
                ]
            ]);
    }

    /**
     * Tests that attempting to update a contact without being authenticated
     * returns a 401 unauthenticated response.
     *
     * This test seeds the database with users and contacts, attempts to update
     * a contact without providing a JWT token, and checks that the response
     * is 401 with the appropriate authentication error message.
     */
    public function testUpdateUnauthenticated()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update Contact
        $response = $this->put(
            '/api/contacts/' . $contact->id,
            [
                'first_name' => 'Test Updated',
                'last_name' => 'User Updated',
                'phone' => '1234567891',
                'email' => 'test_user_update@mail.com',
            ],
            []
        );

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => [
                        'Unauthenticated.'
                    ]
                ]
            ]);
    }

    public function testUpdateValidationError()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Update Contact
        $response = $this->put(
            '/api/contacts/' . $contact->id,
            [
                'first_name' => '',
                'last_name' => 'User',
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
                    'first_name' => [
                        'The first name field is required.'
                    ]
                ]
            ]);
    }

    /**
     * Tests that deleting a contact returns a success response.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * deletes a contact, and checks that the response is 200 with a JSON
     * response indicating success.
     */
    public function testDeleteSuccess()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Delete Contact
        $response = $this->delete('/api/contacts/' . $contact->id, [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    /**
     * Tests that deleting a non-existent contact returns a 404 not found response.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * deletes a contact that does not exist, and checks that the response is
     * 404 with a JSON response indicating that the contact was not found.
     */
    public function testDeleteNotFound()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Delete Contact
        $response = $this->delete('/api/contacts/' . ($contact->id + 2), [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    /**
     * Tests that searching for a contact by first name returns a paginated list of contacts whose first name matches the search term.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * searches for a contact by first name, and checks that the response is 200
     * with a JSON response containing a paginated list of contacts whose
     * first name matches the search term.
     */
    public function testSearchByFirstName()
    {
        // Running the UserSeeder and ContactSeeder
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/search?name=first', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();
        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
    }

    /**
     * Tests that searching for a contact by email returns a paginated list of contacts whose email matches the search term.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * searches for a contact by email, and checks that the response is 200
     * with a JSON response containing a paginated list of contacts whose
     * email matches the search term.
     */
    public function testSearchByEmail()
    {
        // Running the UserSeeder and SearchSeeder
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/search?email=test', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();
        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
    }

    /**
     * Tests that searching for a contact by phone returns a paginated list of contacts whose phone matches the search term.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * searches for a contact by phone, and checks that the response is 200
     * with a JSON response containing a paginated list of contacts whose
     * phone matches the search term.
     */
    public function testSearchByPhone()
    {
        // Running the UserSeeder and SearchSeeder
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/search?phone=089876543', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();
        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
    }

    /**
     * Tests that searching for a contact that does not exist returns a 200 response
     * with an empty list of contacts.
     *
     * This test seeds the database with users and contacts, logs in a user,
     * searches for a contact that does not exist, and checks that the response
     * is 200 with an empty list of contacts.
     */
    public function testSearchNotFound()
    {
        // Running the UserSeeder and SearchSeeder
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/search?name=0tidak ada', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();
        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(0, count($responseData['data']));
        self::assertEquals(0, $responseData['meta']['total']);
    }

    public function testSearchWithPage()
    {
        // Running the UserSeeder and SearchSeeder
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);
        $contact = Contact::query()->limit(1)->first();
        // Get Token
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        // Get Contact
        $response = $this->get('/api/contacts/search?size=1&page=1', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();
        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
        self::assertEquals(1, $responseData['meta']['current_page']);
    }
}
