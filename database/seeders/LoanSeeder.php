<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserTestSeeder::class
        ]);

        $member_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Anggota');
        })->pluck('id')->first();
        $library_id = User::query()->whereHas('userCategory', function ($query) {
            $query->where('name', 'Pustakawan');
        })->pluck('id')->first();

        Loan::create([
            'member_id' => $member_id,
            'librarian_id' => $library_id,
            'loan_date' => '2023-06-01 11:11:11'
        ]);
    }
}
