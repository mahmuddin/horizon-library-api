<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserCategorySeeder::class
        ]);

        $member_category = UserCategory::query()->where('name', 'Anggota')->first();
        $library_category = UserCategory::query()->where('name', 'Pustakawan')->first();

        User::create([
            'name' => 'Test User',
            'email' => 'test@mail.com',
            'username' => 'test',
            'password' => Hash::make('test'),
            'user_category_id' => $member_category->id
        ]);

        User::create([
            'name' => 'Test User 2',
            'email' => 'test2@mail.com',
            'username' => 'test2',
            'password' => Hash::make('test2'),
            'user_category_id' => $library_category->id
        ]);
    }
}
