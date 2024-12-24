<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCategory;
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
     * Test that a loan can be created successfully.
     *
     * This test seeds the database with users, logs in a user, creates a loan,
     * and checks that the response is 201 and that the loan is returned with
     * the correct fields.
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
}
