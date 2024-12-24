<?php

namespace Tests\Feature;

use App\Models\UserCategory;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoanTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess()
    {
        $this->seed(UserTestSeeder::class);

        $token = JWTAuth::attempt(['username' => 'test', 'password' => 'test']);

        $member = UserCategory::query()->where('name', 'Anggota')->first();
        $library = UserCategory::query()->where('name', 'Pustakawan')->first();

        $response = $this->post('/api/loans', [
            'member_id' => $member->id,
            'librarian_id' => $library->id,
            'loan_date' => '2023-06-01 11:11:11'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'member_id' => $member->id,
                'librarian_id' => $library->id,
                'loan_date' => '2023-06-01 11:11:11'
            ]
        ]);
    }
}
