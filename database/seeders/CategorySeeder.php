<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
        [
            'id' => 1,
            'name' => 'Purchase',
            'icon' => '📦',
            'color' => '#00B894',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 2,
            'name' => 'Customer Return',
            'icon' => '↩️',
            'color' => '#55EFC4',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 3,
            'name' => 'Initial Stock',
            'icon' => '🆕',
            'color' => '#00CEC9',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 4,
            'name' => 'Stock Adjustment+',
            'icon' => '➕',
            'color' => '#74B9FF',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 5,
            'name' => 'Production In',
            'icon' => '🏭',
            'color' => '#0984E3',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 6,
            'name' => 'Donation In',
            'icon' => '🎁',
            'color' => '#A29BFE',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 7,
            'name' => 'Other In',
            'icon' => '📥',
            'color' => '#6C5CE7',
            'type' => 'IN',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 8,
            'name' => 'Sale',
            'icon' => '🛒',
            'color' => '#FF7675',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 9,
            'name' => 'Damaged',
            'icon' => '💥',
            'color' => '#D63031',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 10,
            'name' => 'Internal Use',
            'icon' => '🔧',
            'color' => '#E17055',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 11,
            'name' => 'Stock Adjustment-',
            'icon' => '➖',
            'color' => '#FAB1A0',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 12,
            'name' => 'Stolen / Lost',
            'icon' => '❌',
            'color' => '#E84393',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 13,
            'name' => 'Sample',
            'icon' => '🎯',
            'color' => '#FDCB6E',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        [
            'id' => 14,
            'name' => 'Other Out',
            'icon' => '📤',
            'color' => '#FFEAA7',
            'type' => 'OUT',
            'created_at' => '2026-05-12 05:10:19',
            'updated_at' => '2026-05-12 05:10:19',
        ],
        ];

        DB::table('categories')->insert($rows);
    }
}
