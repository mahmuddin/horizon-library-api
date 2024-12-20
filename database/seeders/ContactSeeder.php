<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'test')->first();

        Contact::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test_user@mail.com',
            'phone' => '08987654321',
            'user_id' => $user->id
        ]);
        Contact::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test_user@mail.com',
            'phone' => '08987654322',
            'user_id' => $user->id + 1
        ]);
    }
}
