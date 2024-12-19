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
            'username' => 'test',
            'password' => Hash::make('test'),
            'name' => 'Test User',
            'email' => 'test@mail.com',
        ]);

        User::create([
            'username' => 'test2',
            'password' => Hash::make('test2'),
            'name' => 'Test User 2',
            'email' => 'test2@mail.com',
        ]);
    }
}
