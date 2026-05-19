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
 * Section 4 — Stock Movement (6.92 points, 12 criteria)
 *
 * Catatan (dari StockMovementSeeder):
 *   - Movement id 23 → product_id 1 (milik Budi/user 1)
 *   - Movement id 1  → product_id 24 (milik Dedi/user 5)
 */
class Section4StockMovementTest extends TestCase
{
    use RefreshDatabase, InteractsWithApi, RecordsCriterionScore;

    private function seedBasic(): void
    {
        $this->seedSafe([UserSeeder::class, UnitSeeder::class, CategorySeeder::class, ProductSeeder::class]);
    }

    private function seedAll(): void
    {
        $this->seedBasic();
        $this->seedSafe(StockMovementSeeder::class);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'product_id' => 1,
            'category_id' => 1,
            'quantity' => 50,
            'date' => '2025-07-31',
            'note' => 'Test note',
        ], $overrides);
    }

    /**
     * @criterion 4.1
     * @maxPoints 0.865
     * @partial Half credit if creates but wrong response structure
     */
    public function test_4_1_post_stock_movement_creates_returns_201(): void
    {
        $max = 0.865;
        $this->seedBasic();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->postJson('/api/stock-movements', $this->validPayload(), $this->authHeaders($token)));
        $status = $r ? $r->status() : 0;
        $hasData = $r && $r->json('data.product_id') === 1;

        $earned = match (true) {
            $status === 201 && $hasData => $max,
            $status === 201 || $status === 200 => $max / 2,
            default => 0,
        };

        $this->recordScore('4.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.2
     * @maxPoints 0.577
     */
    public function test_4_2_validates_product_id_must_exist(): void
    {
        $max = 0.577;
        $this->seedBasic();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->postJson('/api/stock-movements', $this->validPayload([
            'product_id' => 99999,
        ]), $this->authHeaders($token)));

        $ok = $r && $r->status() === 422 && $r->json('errors.product_id');
        $earned = $ok ? $max : 0;

        $this->recordScore('4.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.3
     * @maxPoints 0.577
     */
    public function test_4_3_validates_category_id_must_exist(): void
    {
        $max = 0.577;
        $this->seedBasic();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->postJson('/api/stock-movements', $this->validPayload([
            'category_id' => 99999,
        ]), $this->authHeaders($token)));

        $ok = $r && $r->status() === 422 && $r->json('errors.category_id');
        $earned = $ok ? $max : 0;

        $this->recordScore('4.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.4
     * @maxPoints 0.577
     * @partial Half credit if validates integer but not min 1
     */
    public function test_4_4_validates_quantity_integer_min_1(): void
    {
        $max = 0.577;
        $this->seedBasic();
        $token = $this->loginAs();

        // Test: quantity = 0 should fail (min 1)
        $r1 = $this->safe(fn() => $this->postJson('/api/stock-movements', $this->validPayload([
            'quantity' => 0,
        ]), $this->authHeaders($token)));

        // Test: quantity = "abc" should fail (integer)
        $r2 = $this->safe(fn() => $this->postJson('/api/stock-movements', $this->validPayload([
            'quantity' => 'abc',
        ]), $this->authHeaders($token)));

        $minOk = $r1 && $r1->status() === 422 && $r1->json('errors.quantity');
        $intOk = $r2 && $r2->status() === 422 && $r2->json('errors.quantity');

        $earned = match (true) {
            $minOk && $intOk => $max,
            $intOk || $minOk => $max / 2,
            default => 0,
        };

        $this->recordScore('4.4', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.5
     * @maxPoints 0.287
     */
    public function test_4_5_validates_date_format(): void
    {
        $max = 0.287;
        $this->seedBasic();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->postJson('/api/stock-movements', $this->validPayload([
            'date' => '31-07-2025', // wrong format
        ]), $this->authHeaders($token)));

        $ok = $r && $r->status() === 422 && $r->json('errors.date');
        $earned = $ok ? $max : 0;

        $this->recordScore('4.5', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.6
     * @maxPoints 0.288
     */
    public function test_4_6_note_field_is_optional(): void
    {
        $max = 0.288;
        $this->seedBasic();
        $token = $this->loginAs();

        $payload = $this->validPayload();
        unset($payload['note']);

        $r = $this->safe(fn() => $this->postJson('/api/stock-movements', $payload, $this->authHeaders($token)));

        $ok = $r && in_array($r->status(), [200, 201]);
        $earned = $ok ? $max : 0;

        $this->recordScore('4.6', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.7
     * @maxPoints 0.577
     */
    public function test_4_7_delete_own_movement_returns_200(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs(); // budi

        // Movement 23 → product 1 (budi's)
        $r = $this->safe(fn() => $this->deleteJson('/api/stock-movements/23', [], $this->authHeaders($token)));
        $earned = ($r && $r->status() === 200) ? $max : 0;

        $this->recordScore('4.7', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.8
     * @maxPoints 0.577
     * @partial Half credit if only one of 403/404 implemented
     */
    public function test_4_8_delete_returns_403_or_404_correctly(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs(); // budi

        // Movement 1 → product 24 (dedi's) → 403
        $forbidden = $this->safe(fn() => $this->deleteJson('/api/stock-movements/1', [], $this->authHeaders($token)));
        // Non-existent → 404
        $notFound = $this->safe(fn() => $this->deleteJson('/api/stock-movements/999999', [], $this->authHeaders($token)));

        $f403 = $forbidden && $forbidden->status() === 403;
        $f404 = $notFound && $notFound->status() === 404;

        $earned = match (true) {
            $f403 && $f404 => $max,
            $f403 || $f404 => $max / 2,
            default => 0,
        };

        $this->recordScore('4.8', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.9
     * @maxPoints 0.865
     * @partial Half credit if pagination present but default wrong
     */
    public function test_4_9_get_movements_paginated_default_25(): void
    {
        $max = 0.865;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->getJson('/api/stock-movements', $this->authHeaders($token)));

        $perPage = $r ? $r->json('per_page') : null;
        $hasData = $r && is_array($r->json('data'));
        $hasMeta = $r && ($r->json('current_page') !== null);

        $earned = match (true) {
            $hasData && $hasMeta && $perPage == 25 => $max,
            $hasData && $hasMeta => $max / 2,
            default => 0,
        };

        $this->recordScore('4.9', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.10
     * @maxPoints 0.577
     * @partial Half credit if sorted by created_at instead of date
     */
    public function test_4_10_movements_sorted_by_date_desc(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->getJson('/api/stock-movements', $this->authHeaders($token)));
        $items = $r ? $r->json('data') : [];

        if (!is_array($items) || count($items) < 2) {
            $this->recordScore('4.10', 0);
            $this->assertTrue(true);
            return;
        }

        // Cek apakah descending by date
        $dates = array_map(fn($i) => $i['date'] ?? null, $items);
        $sortedByDateDesc = true;
        for ($i = 0; $i < count($dates) - 1; $i++) {
            if ($dates[$i] && $dates[$i + 1] && strtotime($dates[$i]) < strtotime($dates[$i + 1])) {
                $sortedByDateDesc = false;
                break;
            }
        }

        // Cek apakah descending by created_at (fallback)
        $sortedByCreatedDesc = true;
        for ($i = 0; $i < count($items) - 1; $i++) {
            $a = $items[$i]['created_at'] ?? null;
            $b = $items[$i + 1]['created_at'] ?? null;
            if ($a && $b && strtotime($a) < strtotime($b)) {
                $sortedByCreatedDesc = false;
                break;
            }
        }

        $earned = match (true) {
            $sortedByDateDesc => $max,
            $sortedByCreatedDesc => $max / 2,
            default => 0,
        };

        $this->recordScore('4.10', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.11
     * @maxPoints 0.865
     * @partial Half credit if filters by month OR year but not combined
     */
    public function test_4_11_filter_by_month_and_year(): void
    {
        $max = 0.865;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->getJson('/api/stock-movements?month=7&year=2025', $this->authHeaders($token)));
        $items = $r ? $r->json('data') : [];

        if (!is_array($items)) {
            $this->recordScore('4.11', 0);
            $this->assertTrue(true);
            return;
        }

        // Check semua items date starts with "2025-07"
        $allMatch = collect($items)->every(fn($i) => isset($i['date']) && str_starts_with($i['date'], '2025-07'));

        // Apakah filter month-only juga jalan?
        $r2 = $this->safe(fn() => $this->getJson('/api/stock-movements?month=7', $this->authHeaders($token)));
        $items2 = $r2 ? $r2->json('data') : [];
        $monthOnlyMatch = is_array($items2) && collect($items2)->every(
            fn($i) => isset($i['date']) && str_ends_with(substr($i['date'], 0, 7), '-07')
        );

        $earned = match (true) {
            $allMatch && count($items) > 0 => $max,
            $monthOnlyMatch && count($items2) > 0 => $max / 2,
            default => 0,
        };

        $this->recordScore('4.11', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 4.12
     * @maxPoints 0.288
     * @partial Half credit if only one of product/category nested
     */
    public function test_4_12_response_includes_nested_product_and_category(): void
    {
        $max = 0.288;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->getJson('/api/stock-movements', $this->authHeaders($token)));
        $items = $r ? $r->json('data') : [];

        if (!is_array($items) || empty($items)) {
            $this->recordScore('4.12', 0);
            $this->assertTrue(true);
            return;
        }

        $first = $items[0];
        $hasProduct = isset($first['product']) && is_array($first['product']);
        $hasCategory = isset($first['category']) && is_array($first['category']);

        $earned = match (true) {
            $hasProduct && $hasCategory => $max,
            $hasProduct || $hasCategory => $max / 2,
            default => 0,
        };

        $this->recordScore('4.12', $earned);
        $this->assertTrue(true);
    }
}
