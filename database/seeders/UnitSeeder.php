<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
        [
            'id' => 1,
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'code' => 'PCS',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 2,
            'name' => 'Kilogram',
            'symbol' => 'kg',
            'code' => 'KG',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 3,
            'name' => 'Gram',
            'symbol' => 'gr',
            'code' => 'GR',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 4,
            'name' => 'Liter',
            'symbol' => 'ltr',
            'code' => 'LTR',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 5,
            'name' => 'Mililiter',
            'symbol' => 'ml',
            'code' => 'ML',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 6,
            'name' => 'Box',
            'symbol' => 'box',
            'code' => 'BOX',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 7,
            'name' => 'Pack',
            'symbol' => 'pck',
            'code' => 'PCK',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 8,
            'name' => 'Lusin',
            'symbol' => 'lsn',
            'code' => 'LSN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 9,
            'name' => 'Meter',
            'symbol' => 'm',
            'code' => 'M',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 10,
            'name' => 'Roll',
            'symbol' => 'rl',
            'code' => 'RL',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        ];

        DB::table('units')->insert($rows);
    }
}
