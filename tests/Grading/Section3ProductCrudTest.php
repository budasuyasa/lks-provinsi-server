<?php

namespace Tests\Grading;

use Database\Seeders\ProductSeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Grading\Concerns\InteractsWithApi;
use Tests\Grading\Concerns\RecordsCriterionScore;
use Tests\TestCase;

/**
 * Section 3 — Product CRUD (8.08 points, 12 criteria)
 *
 * Catatan ownership (dari ProductSeeder):
 *   - user_id 1 (budi) → product 1..7
 *   - user_id 2 (siti) → product 8..12
 */
class Section3ProductCrudTest extends TestCase
{
    use RefreshDatabase, InteractsWithApi, RecordsCriterionScore;

    private function seedAll(): void
    {
        $this->seedSafe([UserSeeder::class, UnitSeeder::class, ProductSeeder::class]);
    }

    /**
     * @criterion 3.1
     * @maxPoints 0.866
     * @partial Half credit if endpoint creates but returns wrong status/data
     */
    public function test_3_1_post_product_creates_returns_201(): void
    {
        $max = 0.866;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->postJson('/api/products', [
            'name' => 'Test Product', 'unit_code' => 'PCS',
        ], $this->authHeaders($token)));

        $status = $response ? $response->status() : 0;
        $hasName = $response && $response->json('data.name') === 'Test Product';

        $earned = match (true) {
            $status === 201 && $hasName => $max,
            in_array($status, [200, 201]) => $max / 2,
            default => 0,
        };

        $this->recordScore('3.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.2
     * @maxPoints 0.577
     * @partial Half credit if user_id required in request instead of inferred from token
     */
    public function test_3_2_product_auto_assigned_to_authenticated_user(): void
    {
        $max = 0.577;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs(); // budi → user_id 1

        // Coba kirim user_id berbeda (3), API harus tetap pakai user yang login (1)
        $response = $this->safe(fn() => $this->postJson('/api/products', [
            'name' => 'X', 'unit_code' => 'PCS', 'user_id' => 3,
        ], $this->authHeaders($token)));

        $userId = $response ? $response->json('data.user_id') : null;
        $earned = ($userId === 1) ? $max : (($userId !== null) ? $max / 2 : 0);

        $this->recordScore('3.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.3
     * @maxPoints 0.577
     * @partial Half credit if only one field validated
     */
    public function test_3_3_product_create_validates_name_and_unit_code(): void
    {
        $max = 0.577;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->postJson('/api/products', [
            'unit_code' => 'INVALID_CODE',
        ], $this->authHeaders($token)));

        $status = $r ? $r->status() : 0;
        $errors = $r ? $r->json('errors') : null;

        $nameValidated = $errors && isset($errors['name']);
        $unitValidated = $errors && isset($errors['unit_code']);

        $earned = match (true) {
            $status === 422 && $nameValidated && $unitValidated => $max,
            $status === 422 && ($nameValidated || $unitValidated) => $max / 2,
            default => 0,
        };

        $this->recordScore('3.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.4
     * @maxPoints 0.866
     * @partial Half credit if update works but returns wrong data
     */
    public function test_3_4_put_product_updates_own_returns_200(): void
    {
        $max = 0.866;
        $this->seedAll();
        $token = $this->loginAs(); // budi

        $response = $this->safe(fn() => $this->putJson('/api/products/1', [
            'name' => 'Updated Name',
        ], $this->authHeaders($token)));

        $status = $response ? $response->status() : 0;
        $nameUpdated = $response && $response->json('data.name') === 'Updated Name';

        $earned = match (true) {
            $status === 200 && $nameUpdated => $max,
            $status === 200 => $max / 2,
            default => 0,
        };

        $this->recordScore('3.4', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.5
     * @maxPoints 0.577
     * @partial Half credit if returns 401 or 404 instead of 403
     */
    public function test_3_5_update_returns_403_for_other_users_product(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs(); // budi

        // Product 8 milik siti (user 2)
        $r = $this->safe(fn() => $this->putJson('/api/products/8', [
            'name' => 'X',
        ], $this->authHeaders($token)));

        $status = $r ? $r->status() : 0;
        $earned = match ($status) {
            403 => $max,
            401, 404 => $max / 2,
            default => 0,
        };

        $this->recordScore('3.5', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.6
     * @maxPoints 0.288
     */
    public function test_3_6_update_returns_404_for_non_existent(): void
    {
        $max = 0.288;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn() => $this->putJson('/api/products/99999', [
            'name' => 'X',
        ], $this->authHeaders($token)));

        $earned = ($r && $r->status() === 404) ? $max : 0;

        $this->recordScore('3.6', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.7
     * @maxPoints 0.866
     * @partial Half credit if hard-deletes instead of soft-delete
     */
    public function test_3_7_delete_product_soft_deletes_own(): void
    {
        $max = 0.866;
        $this->seedAll();
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->deleteJson('/api/products/1', [], $this->authHeaders($token)));
        $status = $response ? $response->status() : 0;

        // Cek apakah soft delete (deleted_at terisi di DB) atau hard delete
        $row = null;
        try {
            $row = \DB::table('products')->where('id', 1)->first();
        } catch (\Throwable $e) {}

        $softDeleted = $row && property_exists($row, 'deleted_at') && $row->deleted_at !== null;
        $hardDeleted = $row === null;

        $earned = match (true) {
            $status === 200 && $softDeleted => $max,
            $status === 200 && $hardDeleted => $max / 2,
            $status === 200 => $max / 2,
            default => 0,
        };

        $this->recordScore('3.7', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.8
     * @maxPoints 0.577
     * @partial Half credit if only one of 403/404 implemented correctly
     */
    public function test_3_8_delete_returns_403_or_404_correctly(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs();

        $forbidden = $this->safe(fn() => $this->deleteJson('/api/products/8', [], $this->authHeaders($token)));
        $notFound = $this->safe(fn() => $this->deleteJson('/api/products/99999', [], $this->authHeaders($token)));

        $f403 = $forbidden && $forbidden->status() === 403;
        $f404 = $notFound && $notFound->status() === 404;

        $earned = match (true) {
            $f403 && $f404 => $max,
            $f403 || $f404 => $max / 2,
            default => 0,
        };

        $this->recordScore('3.8', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.9
     * @maxPoints 0.866
     * @partial Half credit if returns all users' products instead of filtered
     */
    public function test_3_9_get_products_returns_only_own(): void
    {
        $max = 0.866;
        $this->seedAll();
        $token = $this->loginAs(); // budi

        $response = $this->safe(fn() => $this->getJson('/api/products', $this->authHeaders($token)));
        $products = $response ? $response->json('data.products') : null;

        if (!is_array($products)) {
            $this->recordScore('3.9', 0);
            $this->assertTrue(true);
            return;
        }

        $allOwn = collect($products)->every(fn($p) => ($p['user_id'] ?? null) === 1);
        $total = count($products);

        $earned = match (true) {
            $allOwn && $total === 7 => $max, // budi has 7 products
            $allOwn => $max * 0.75,
            $total > 7 => $max / 2, // returns all users
            default => 0,
        };

        $this->recordScore('3.9', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.10
     * @maxPoints 0.866
     * @partial Half credit if field present but value wrong; quarter credit if field missing
     */
    public function test_3_10_get_products_includes_current_stock(): void
    {
        $max = 0.866;
        $this->seedAll();
        $this->seedSafe(\Database\Seeders\CategorySeeder::class);
        $this->seedSafe(\Database\Seeders\StockMovementSeeder::class);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->getJson('/api/products', $this->authHeaders($token)));
        $products = $response ? $response->json('data.products') : null;

        if (!is_array($products) || empty($products)) {
            $this->recordScore('3.10', 0);
            $this->assertTrue(true);
            return;
        }

        $hasField = array_key_exists('current_stock', $products[0]);
        // Calculated value: untuk verify correctness, sum movements
        $product1Stock = collect($products)->firstWhere('id', 1)['current_stock'] ?? null;

        $earned = match (true) {
            $hasField && is_numeric($product1Stock) && $product1Stock !== 0 => $max,
            $hasField && is_numeric($product1Stock) => $max / 2,
            $hasField => $max / 2,
            default => $max / 4,
        };

        $this->recordScore('3.10', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.11
     * @maxPoints 0.577
     * @partial Half credit if returns data but missing current_stock
     */
    public function test_3_11_get_product_detail_with_current_stock(): void
    {
        $max = 0.577;
        $this->seedAll();
        $this->seedSafe(\Database\Seeders\CategorySeeder::class);
        $this->seedSafe(\Database\Seeders\StockMovementSeeder::class);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->getJson('/api/products/1', $this->authHeaders($token)));
        $data = $response ? $response->json('data') : null;

        $hasData = $data && isset($data['id'], $data['name']);
        $hasStock = $data && array_key_exists('current_stock', $data);

        $earned = match (true) {
            $hasData && $hasStock => $max,
            $hasData => $max / 2,
            default => 0,
        };

        $this->recordScore('3.11', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 3.12
     * @maxPoints 0.577
     * @partial Half credit if only one handled correctly
     */
    public function test_3_12_detail_returns_403_or_404_correctly(): void
    {
        $max = 0.577;
        $this->seedAll();
        $token = $this->loginAs();

        $forbidden = $this->safe(fn() => $this->getJson('/api/products/8', $this->authHeaders($token)));
        $notFound = $this->safe(fn() => $this->getJson('/api/products/99999', $this->authHeaders($token)));

        $f403 = $forbidden && $forbidden->status() === 403;
        $f404 = $notFound && $notFound->status() === 404;

        $earned = match (true) {
            $f403 && $f404 => $max,
            $f403 || $f404 => $max / 2,
            default => 0,
        };

        $this->recordScore('3.12', $earned);
        $this->assertTrue(true);
    }
}
