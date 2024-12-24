<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\User;
use App\Models\UserCategory;
use Database\Seeders\LoanSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class LoanTest extends TestCase
{
    /**
     * Tests that a loan can be created successfully.
     *
     * This test logs in a user and then creates a loan with the required
     * fields. It checks that the response is 201 and that the loan is returned
     * in the response with the correct fields.
     */
    public function testCreateSuccess()
    {
        $this->seed(UserTestSeeder::class);

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $member_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Anggota');
        })->pluck('id')->first();
        $library_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Pustakawan');
        })->pluck('id')->first();

        $response = $this->post('/api/loans', [
            'member_id' => $member_id,
            'librarian_id' => $library_id,
            'loan_date' => '2023-06-01 11:11:11'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'loan_date' => '2023-06-01 11:11:11',
                'return_date' => null,
            ]
        ]);
    }

    /**
     * Tests that creating a loan fails when required fields are empty.
     *
     * This test seeds the database with users, logs in a user, creates a loan
     * with empty required fields, and checks that the response is 400 and
     * contains the appropriate validation error messages.
     */
    public function testCreateFail()
    {
        $this->seed(UserTestSeeder::class);

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->post('/api/loans', [
            'member_id' => '',
            'librarian_id' => '',
            'loan_date' => ''
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(400)->assertJson([
            'errors' => [
                'member_id' => ['The member id field is required.'],
                'librarian_id' => ['The librarian id field is required.'],
                'loan_date' => ['The loan date field is required.']
            ]
        ]);
    }

    /**
     * Tests that creating a loan fails when the user is not authenticated.
     *
     * This test seeds the database with users, attempts to create a loan
     * without providing a JWT token, and checks that the response is 401
     * with the appropriate authentication error message.
     */
    public function testCreateUnauthenticated()
    {
        $this->seed(UserTestSeeder::class);

        $member_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Anggota');
        })->pluck('id')->first();
        $library_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Pustakawan');
        })->pluck('id')->first();

        $response = $this->post('/api/loans', [
            'member_id' => $member_id,
            'librarian_id' => $library_id,
            'loan_date' => '2023-06-01 11:11:11'
        ]);

        $response->assertStatus(401)->assertJson([
            'errors' => [
                'message' => ['Unauthenticated.']
            ]
        ]);
    }

    /**
     * Test that a loan can be retrieved successfully.
     *
     * This test seeds the database with loans, logs in a user,
     * retrieves a loan by its ID, and checks that the response is 200
     * with the correct loan details in the response JSON.
     */
    public function testGetSuccess()
    {
        $this->seed(LoanSeeder::class);

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $loan = Loan::query()->limit(1)->first();

        $response = $this->get('/api/loans/' . $loan->id, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'loan_date' => '2023-06-01 11:11:11',
                'return_date' => null
            ]
        ]);
    }

    public function testGetNotFound()
    {
        $this->seed(LoanSeeder::class);

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->get('/api/loans/' . 100, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found.']
            ]
        ]);
    }

    /**
     * Tests that retrieving a loan fails when the user is not authenticated.
     *
     * This test seeds the database with loans, attempts to retrieve a loan
     * without providing a JWT token, and checks that the response is 401
     * with the appropriate authentication error message.
     */
    public function testGetUnauthorized()
    {
        $this->seed(LoanSeeder::class);

        $loan = Loan::query()->limit(1)->first();

        $response = $this->get('/api/loans/' . $loan->id);

        $response->assertStatus(401)->assertJson([
            'errors' => [
                'message' => ['Unauthenticated.']
            ]
        ]);
    }

    /**
     * Tests that retrieving a loan fails when the provided JWT token is invalid.
     *
     * This test seeds the database with loans, attempts to retrieve a loan
     * with an invalid JWT token, and checks that the response is 401
     * with the appropriate authentication error message.
     */
    public function testGetInvalidToken()
    {
        $this->seed(LoanSeeder::class);

        $loan = Loan::query()->limit(1)->first();

        $response = $this->get('/api/loans/' . $loan->id, [
            'Authorization' => 'Bearer invalidtoken'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthenticated.']
                ]
            ]);
    }

    /**
     * Tests that retrieving a list of loans works successfully.
     *
     * This test seeds the database with loans, logs in a user,
     * retrieves the list of loans, and checks that the response is 200
     * with the correct loan details in the response JSON.
     */
    public function testListSuccess()
    {
        $this->seed(LoanSeeder::class);

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->get('/api/loans', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'loan_date' => '2023-06-01 11:11:11',
                    'return_date' => null
                ]
            ]
        ]);
    }

    /**
     * Tests that updating a loan works successfully.
     *
     * This test seeds the database with loans, logs in a user, updates a loan
     * with the required fields, and checks that the response is 200 with the
     * updated loan in JSON format.
     */
    public function testUpdateSuccess()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $member_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Anggota');
        })->pluck('id')->first();
        $library_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Pustakawan');
        })->pluck('id')->first();

        $response = $this->put('/api/loans/' . $loan->id, [
            'loan_date' => '2023-06-01 11:11:11',
            'return_date' => '2023-06-01 12:11:11',
            'member_id' => $member_id,
            'librarian_id' => $library_id
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'loan_date' => '2023-06-01 11:11:11',
                'return_date' => '2023-06-01 12:11:11'
            ]
        ]);
    }

    /**
     * Tests that updating a loan with invalid data fails with a 400 error.
     *
     * This test seeds the database with loans, logs in a user, attempts to update
     * a loan with empty required fields, and checks that the response is 400 with
     * appropriate validation error messages.
     */
    public function testUpdateValidaitonError()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->put('/api/loans/' . $loan->id, [
            'loan_date' => '2023-06-01 11:11:11',
            'return_date' => '2023-06-01 12:11:11',
            'member_id' => '',
            'librarian_id' => ''
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(400)->assertJson([
            'errors' => [
                'member_id' => ['The member id field is required.'],
                'librarian_id' => ['The librarian id field is required.']
            ]
        ]);
    }

    /**
     * Tests that deleting a loan returns a success response.
     *
     * This test seeds the database with loans, logs in a user,
     * deletes a loan by its ID, and checks that the response is 200
     * with a JSON response indicating success.
     */
    public function testDeleteSuccess()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->delete('/api/loans/' . $loan->id, [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }

    /**
     * Tests that deleting a loan with a non-existent ID returns a 404 not found response.
     *
     * This test seeds the database with loans, logs in a user,
     * attempts to delete a loan with a non-existent ID, and checks that the response is 404
     * with a JSON response indicating that the loan was not found.
     */
    public function testDeleteNotFound()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $response = $this->delete('/api/loans/' . $loan->id + 1, [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found.']
            ]
        ]);
    }

    /**
     * Tests that searching for a loan by member ID returns the correct loan details.
     *
     * This test seeds the database with loans, logs in a user, searches for a loan
     * using the member ID, and checks that the response is 200 with the correct
     * loan details in the response JSON.
     */
    public function testSearchByMemberId()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?member_id=' . $loan->member_id, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'loan_date' => '2023-06-01 11:11:11',
                    'return_date' => null
                ]
            ]
        ]);
    }

    public function testSearchByLibrarianId()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?librarian_id=' . $loan->librarian_id, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'loan_date' => '2023-06-01 11:11:11',
                    'return_date' => null
                ]
            ]
        ]);
    }

    public function testSearchByMemberIdAndLibrarianId()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?member_id=' . $loan->member_id . '&librarian_id=' . $loan->librarian_id, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'loan_date' => '2023-06-01 11:11:11',
                    'return_date' => null
                ]
            ]
        ]);
    }

    public function testSeachByLoanDate()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?loan_date=' . $loan->loan_date, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'loan_date' => '2023-06-01 11:11:11',
                    'return_date' => null
                ]
            ]
        ]);
    }

    public function testSearchByMemberIdAndLibrarianIdAndLoanDate()
    {
        $this->seed(LoanSeeder::class);
        $loan = Loan::query()->limit(1)->first();

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?member_id=' . $loan->member_id . '&librarian_id=' . $loan->librarian_id . '&loan_date=' . $loan->loan_date, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'loan_date' => '2023-06-01 11:11:11',
                    'return_date' => null
                ]
            ]
        ]);
    }

    public function testSearchNotFound()
    {
        $this->seed(LoanSeeder::class);
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?loan_date=2000-06-01 11:11:11', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => []
        ]);
    }

    public function testSearchWithPage()
    {
        $this->seed(LoanSeeder::class);
        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);
        $response = $this->get('/api/loans/search?loan_date=2000-06-01 11:11:11&page=1&size=10', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => []
        ]);


        $responseData = $response->json();
        self::assertEquals(1, count($responseData['data']));
        self::assertEquals(1, $responseData['meta']['total']);
        self::assertEquals(1, $responseData['meta']['current_page']);
    }
}
