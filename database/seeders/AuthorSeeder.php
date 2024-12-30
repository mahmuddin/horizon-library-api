<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Author::create([
            'name' => 'Test Author',
            'address' => 'Test Address',
            'phone' => '08987654321',
            'email' => 'test@mail.com',
            'website' => 'https://www.test.com',
            'bio' => 'Test Bio',
            'profile_image' => '',
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
            ]
        ]);
    }
}
