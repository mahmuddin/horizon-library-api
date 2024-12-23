<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@mail.com',
            'username' => 'test',
            'password' => Hash::make('test'),
        ]);

        User::create([
            'name' => 'Test User 2',
            'email' => 'test2@mail.com',
            'username' => 'test2',
            'password' => Hash::make('test2'),
        ]);
    }
}
