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
 * Section 5 — Stock Report (3.46 points, 6 criteria)
 *
 * Response shape acuan (json-response.pdf):
 *   E1a /api/reports/summary-by-category/out 200:
 *       { data:{ summary:[ { category:{ id,name,icon,color,type:"OUT",created_at,updated_at }, quantity } ] } }
 *   E2a /api/reports/summary-by-category/in 200: idem dengan type:"IN".
 */
class Section5StockReportTest extends TestCase
{
    use InteractsWithApi, RecordsCriterionScore, RefreshDatabase;

    private function seedAll(): void
    {
        $this->seedSafe([
            UserSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            StockMovementSeeder::class,
        ]);
    }

    /**
     * @criterion 5.1
     *
     * @maxPoints 0.577
     */
    public function test_5_1_get_summary_out_returns_200_with_array(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/out', $this->authHeaders($token)));

        if ($this->isServerError($r)) {
            $this->recordScore('5.1', 0);
            $this->assertTrue(true);

            return;
        }

        $summary = $r->json('data.summary');
        $ok = $r->status() === 200 && is_array($summary);
        $earned = $ok ? $max : 0;

        $this->recordScore('5.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 5.2
     *
     * @maxPoints 0.865
     *
     * @partial Half credit if structure correct but quantity sum wrong
     */
    public function test_5_2_out_summary_has_category_and_quantity(): void
    {
        $max = 0.865;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/out', $this->authHeaders($token)));

        if ($this->isServerError($r) || $r->status() !== 200) {
            $this->recordScore('5.2', 0);
            $this->assertTrue(true);

            return;
        }

        $summary = $r->json('data.summary');
        if (! is_array($summary) || empty($summary)) {
            $this->recordScore('5.2', 0);
            $this->assertTrue(true);

            return;
        }

        $hasStructure = collect($summary)->every(
            fn ($item) => is_array($item) && isset($item['category']) && isset($item['quantity']) && is_numeric($item['quantity'])
        );
        $allOut = collect($summary)->every(
            fn ($item) => is_array($item) && isset($item['category']['type']) && $item['category']['type'] === 'OUT'
        );
        $hasQty = collect($summary)->sum(fn ($i) => is_array($i) && is_numeric($i['quantity'] ?? null) ? $i['quantity'] : 0) > 0;

        $earned = match (true) {
            $hasStructure && $allOut && $hasQty => $max,
            $hasStructure && $allOut => $max / 2,
            $hasStructure => $max / 2,
            default => 0,
        };

        $this->recordScore('5.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 5.3
     *
     * @maxPoints 0.288
     *
     * @partial Half credit if only month OR year filter works
     */
    public function test_5_3_out_supports_month_year_filter(): void
    {
        $max = 0.288;
        $this->seedAll();
        $token = $this->loginAs();

        $rBoth = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/out?month=7&year=2025', $this->authHeaders($token)));
        $rNone = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/out', $this->authHeaders($token)));

        if ($this->isServerError($rBoth) || $this->isServerError($rNone)) {
            $this->recordScore('5.3', 0);
            $this->assertTrue(true);

            return;
        }

        $bothOk = $rBoth->status() === 200;
        $noneOk = $rNone->status() === 200;

        $bothQty = $bothOk ? collect($rBoth->json('data.summary') ?: [])->sum(fn ($i) => $i['quantity'] ?? 0) : null;
        $noneQty = $noneOk ? collect($rNone->json('data.summary') ?: [])->sum(fn ($i) => $i['quantity'] ?? 0) : null;

        $filterWorks = $bothOk && $noneOk && is_numeric($bothQty) && is_numeric($noneQty) && $bothQty < $noneQty;

        $earned = match (true) {
            $filterWorks => $max,
            $bothOk => $max / 2,
            default => 0,
        };

        $this->recordScore('5.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 5.4
     *
     * @maxPoints 0.577
     */
    public function test_5_4_get_summary_in_returns_200_with_array(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/in', $this->authHeaders($token)));

        if ($this->isServerError($r)) {
            $this->recordScore('5.4', 0);
            $this->assertTrue(true);

            return;
        }

        $summary = $r->json('data.summary');
        $ok = $r->status() === 200 && is_array($summary);
        $earned = $ok ? $max : 0;

        $this->recordScore('5.4', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 5.5
     *
     * @maxPoints 0.865
     *
     * @partial Half credit if structure correct but quantity sum wrong
     */
    public function test_5_5_in_summary_has_category_and_quantity(): void
    {
        $max = 0.865;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/in', $this->authHeaders($token)));

        if ($this->isServerError($r) || $r->status() !== 200) {
            $this->recordScore('5.5', 0);
            $this->assertTrue(true);

            return;
        }

        $summary = $r->json('data.summary');
        if (! is_array($summary) || empty($summary)) {
            $this->recordScore('5.5', 0);
            $this->assertTrue(true);

            return;
        }

        $hasStructure = collect($summary)->every(
            fn ($item) => is_array($item) && isset($item['category']) && isset($item['quantity']) && is_numeric($item['quantity'])
        );
        $allIn = collect($summary)->every(
            fn ($item) => is_array($item) && isset($item['category']['type']) && $item['category']['type'] === 'IN'
        );
        $hasQty = collect($summary)->sum(fn ($i) => is_array($i) && is_numeric($i['quantity'] ?? null) ? $i['quantity'] : 0) > 0;

        $earned = match (true) {
            $hasStructure && $allIn && $hasQty => $max,
            $hasStructure && $allIn => $max / 2,
            $hasStructure => $max / 2,
            default => 0,
        };

        $this->recordScore('5.5', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 5.6
     *
     * @maxPoints 0.288
     *
     * @partial Half credit if only month OR year filter works
     */
    public function test_5_6_in_supports_month_year_filter(): void
    {
        $max = 0.288;
        $this->seedAll();
        $token = $this->loginAs();

        $rBoth = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/in?month=7&year=2025', $this->authHeaders($token)));
        $rNone = $this->safe(fn () => $this->getJson('/api/reports/summary-by-category/in', $this->authHeaders($token)));

        if ($this->isServerError($rBoth) || $this->isServerError($rNone)) {
            $this->recordScore('5.6', 0);
            $this->assertTrue(true);

            return;
        }

        $bothOk = $rBoth->status() === 200;
        $noneOk = $rNone->status() === 200;

        $bothQty = $bothOk ? collect($rBoth->json('data.summary') ?: [])->sum(fn ($i) => $i['quantity'] ?? 0) : null;
        $noneQty = $noneOk ? collect($rNone->json('data.summary') ?: [])->sum(fn ($i) => $i['quantity'] ?? 0) : null;

        $filterWorks = $bothOk && $noneOk && is_numeric($bothQty) && is_numeric($noneQty) && $bothQty < $noneQty;

        $earned = match (true) {
            $filterWorks => $max,
            $bothOk => $max / 2,
            default => 0,
        };

        $this->recordScore('5.6', $earned);
        $this->assertTrue(true);
    }
}
