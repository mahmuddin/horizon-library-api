<?php

namespace Database\Seeders;

use App\Models\UserCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserCategory::create(
            [
                'name' => 'Superadmin',
                'description' => 'Fungsi: Tingkatan admin tertinggi, biasanya digunakan untuk organisasi besar.',
            ]
        );

        UserCategory::create(
            [
                'name' => 'Administrator',
                'description' => 'Fungsi: Mengelola seluruh sistem e-library, termasuk pengguna, koleksi digital, dan pengaturan aplikasi.',
            ]
        );

        UserCategory::create(
            [
                'name' => 'Pustakawan',
                'description' => 'Fungsi: Mengelola koleksi dan membantu pengguna dalam mengakses informasi.',
            ]
        );

        UserCategory::create(
            [
                'name' => 'Anggota',
                'description' => 'Fungsi: Pengguna utama yang mengakses konten perpustakaan.',
            ]
        );

        UserCategory::create(
            [
                'name' => 'Pengunjung Umum',
                'description' => 'Pengguna yang tidak memiliki akun atau belum login ke sistem.',
            ]
        );

        UserCategory::create(
            [
                'name' => 'Contributor',
                'description' => 'Pengguna yang bertanggung jawab menambahkan koleksi konten baru, seperti penulis atau penerbit',
            ]
        );
    }
}
