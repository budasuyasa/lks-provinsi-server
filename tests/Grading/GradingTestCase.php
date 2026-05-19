<?php

namespace Tests\Grading;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\StockMovementSeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Grading\Concerns\InteractsWithApi;
use Tests\Grading\Concerns\RecordsCriterionScore;
use Tests\TestCase;

/**
 * Base test case untuk grading suite.
 *
 * Setiap test mulai dengan baseline data lengkap (sesuai resources/db-dump.sql):
 *   - 5 users      → id 1=Budi, 2=Siti, 3=Andi, 4=Rina, 5=Dedi
 *   - 10 units     → PCS, KG, GR, LTR, ML, BOX, PCK, LSN, M, RL
 *   - 14 categories → id 1..7 = IN, id 8..14 = OUT
 *   - 25 products  → 1..7=Budi, 8..12=Siti, 13..17=Andi, 18..22=Rina, 23..25=Dedi
 *   - ~625 stock movements
 *
 * RefreshDatabase memberi isolasi antar test sehingga urutan eksekusi tidak
 * mempengaruhi hasil (test destruktif tidak akan merusak prerequisite test lain).
 */
abstract class GradingTestCase extends TestCase
{
    use InteractsWithApi, RecordsCriterionScore, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Selalu seed full baseline supaya semua test punya prerequisite data.
        $this->seed([
            UserSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            StockMovementSeeder::class,
        ]);
    }
}
