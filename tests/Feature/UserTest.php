<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $response = $this->post('/api/users/register', [
            'name' => 'Test',
            'email' => 'test@mail.com',
            'username' => 'test',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Test',
                    'email' => 'test@mail.com',
                    'username' => 'test',
                ]
            ]);
    }

    // public function testRegisterFailed()
    // {
    //     $response = $this->post('/api/users/register', [
    //         'name' => 'Test',
    //         'email' => 'test@mail.com',
    //         'username' => 'test',
    //         'password' => '123456',
    //         'password_confirmation' => '123456'
    //     ]);
    // }
}
