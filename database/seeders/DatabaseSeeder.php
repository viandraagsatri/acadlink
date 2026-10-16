<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $akun = [
            ['Mahasiswa Satu', '1000000001', 'mahasiswa1@acadlink.test', 'mahasiswa'],
            ['Mahasiswa Dua',  '1000000002', 'mahasiswa2@acadlink.test', 'mahasiswa'],
            ['Dosen Satu',     '2000000001', 'dosen1@acadlink.test',     'dosen'],
            ['Dosen Dua',      '2000000002', 'dosen2@acadlink.test',     'dosen'],
        ];

        foreach ($akun as [$name, $nim_nip, $email, $role]) {
            User::create([
                'name'     => $name,
                'nim_nip'  => $nim_nip,
                'email'    => $email,
                'role'     => $role,
                'password' => Hash::make('password'),
            ]);
        }
    }
}