<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'id' => 1,
                'name' => 'Budi',
                'email' => 'budi@webtech.id',
                'email_verified_at' => null,
                'password' => \Hash::make('password'),
                'remember_token' => null,
                'created_at' => '2026-05-12 05:10:19',
                'updated_at' => '2026-05-12 05:10:19',
            ],
            [
                'id' => 2,
                'name' => 'Siti',
                'email' => 'siti@webtech.id',
                'email_verified_at' => null,
                'password' => \Hash::make('password'),
                'remember_token' => null,
                'created_at' => '2026-05-12 05:10:19',
                'updated_at' => '2026-05-12 05:10:19',
            ],
            [
                'id' => 3,
                'name' => 'Andi',
                'email' => 'andi@webtech.id',
                'email_verified_at' => null,
                'password' => \Hash::make('password'),
                'remember_token' => null,
                'created_at' => '2026-05-12 05:10:19',
                'updated_at' => '2026-05-12 05:10:19',
            ],
            [
                'id' => 4,
                'name' => 'Rina',
                'email' => 'rina@webtech.id',
                'email_verified_at' => null,
                'password' => \Hash::make('password'),
                'remember_token' => null,
                'created_at' => '2026-05-12 05:10:19',
                'updated_at' => '2026-05-12 05:10:19',
            ],
            [
                'id' => 5,
                'name' => 'Dedi',
                'email' => 'dedi@webtech.id',
                'email_verified_at' => null,
                'password' => \Hash::make('password'),
                'remember_token' => null,
                'created_at' => '2026-05-12 05:10:19',
                'updated_at' => '2026-05-12 05:10:19',
            ],
        ];

        DB::table('users')->insert($rows);
    }
}
