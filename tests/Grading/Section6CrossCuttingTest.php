<?php

namespace Tests\Grading;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\StockMovementSeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Grading\Concerns\InteractsWithApi;
use Tests\Grading\Concerns\RecordsCriterionScore;
use Tests\TestCase;

/**
 * Section 6 — Cross-cutting Quality (3.47 points, 5 criteria)
 *
 * Pesan kanonik (json-response.pdf):
 *   - 403 → message:"Forbidden access"
 *   - 404 → message:"Not found"
 *   - 422 → message:"Invalid field", errors as object per-field
 */
class Section6CrossCuttingTest extends TestCase
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
     * @criterion 6.1
     *
     * @maxPoints 1.158
     *
     * @partial Half credit if works for IN only or OUT only; quarter if value drifts
     */
    public function test_6_1_current_stock_in_increases_out_decreases(): void
    {
        $max = 1.158;
        $this->seedAll();
        $token = $this->loginAs();

        $r0 = $this->safe(fn () => $this->getJson('/api/products/1', $this->authHeaders($token)));
        if ($this->isServerError($r0) || $r0->status() !== 200) {
            $this->recordScore('6.1', 0);
            $this->assertTrue(true);

            return;
        }

        $stock0 = $r0->json('data.current_stock');
        if (! is_numeric($stock0)) {
            $this->recordScore('6.1', 0);
            $this->assertTrue(true);

            return;
        }

        // IN movement (category_id 1 = Purchase, IN type)
        $rIn = $this->safe(fn () => $this->postJson('/api/stock-movements', [
            'product_id' => 1, 'category_id' => 1, 'quantity' => 100, 'date' => '2025-09-01',
        ], $this->authHeaders($token)));

        $r1 = $this->safe(fn () => $this->getJson('/api/products/1', $this->authHeaders($token)));
        if ($this->isServerError($rIn) || $this->isServerError($r1)) {
            $this->recordScore('6.1', 0);
            $this->assertTrue(true);

            return;
        }
        $stock1 = $r1->json('data.current_stock');
        $inIncreased = is_numeric($stock1) && ($stock1 - $stock0) == 100;

        // OUT movement (category_id 8 = Sale, OUT type)
        $rOut = $this->safe(fn () => $this->postJson('/api/stock-movements', [
            'product_id' => 1, 'category_id' => 8, 'quantity' => 30, 'date' => '2025-09-02',
        ], $this->authHeaders($token)));

        $r2 = $this->safe(fn () => $this->getJson('/api/products/1', $this->authHeaders($token)));
        if ($this->isServerError($rOut) || $this->isServerError($r2)) {
            $this->recordScore('6.1', 0);
            $this->assertTrue(true);

            return;
        }
        $stock2 = $r2->json('data.current_stock');
        $outDecreased = is_numeric($stock2) && is_numeric($stock1) && ($stock1 - $stock2) == 30;

        $earned = match (true) {
            $inIncreased && $outDecreased => $max,
            $inIncreased || $outDecreased => $max / 2,
            default => 0,
        };

        $this->recordScore('6.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 6.2
     *
     * @maxPoints 0.578
     *
     * @partial Half credit if soft delete works but deleted still shown in GET
     */
    public function test_6_2_soft_delete_preserves_deleted_at(): void
    {
        $max = 0.578;
        $this->seedAll();
        $token = $this->loginAs();

        $delete = $this->safe(fn () => $this->deleteJson('/api/products/1', [], $this->authHeaders($token)));
        if ($this->isServerError($delete)) {
            $this->recordScore('6.2', 0);
            $this->assertTrue(true);

            return;
        }

        $row = null;
        try {
            $row = DB::table('products')->where('id', 1)->first();
        } catch (\Throwable $e) {
        }

        $hasDeletedAt = $row && property_exists($row, 'deleted_at') && $row->deleted_at !== null;

        $r = $this->safe(fn () => $this->getJson('/api/products', $this->authHeaders($token)));
        if ($this->isServerError($r)) {
            $this->recordScore('6.2', 0);
            $this->assertTrue(true);

            return;
        }

        $products = $r->json('data.products') ?: [];
        $idsReturned = is_array($products) ? collect($products)->pluck('id')->all() : [];
        $notInList = ! in_array(1, $idsReturned, true);

        $earned = match (true) {
            $hasDeletedAt && $notInList => $max,
            $hasDeletedAt => $max / 2, // soft delete benar tapi masih muncul di list
            default => 0,
        };

        $this->recordScore('6.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 6.3
     *
     * @maxPoints 0.578
     *
     * @partial Quarter credit per missing field (max half deducted)
     */
    public function test_6_3_pagination_includes_all_metadata(): void
    {
        $max = 0.578;
        $this->seedAll();
        $token = $this->loginAs();

        $r = $this->safe(fn () => $this->getJson('/api/stock-movements', $this->authHeaders($token)));

        if ($this->isServerError($r) || $r->status() !== 200) {
            $this->recordScore('6.3', 0);
            $this->assertTrue(true);

            return;
        }

        $required = ['current_page', 'last_page', 'per_page', 'from', 'to', 'total', 'data'];
        $presentCount = count(array_filter($required, fn ($f) => $r->json($f) !== null));

        if ($presentCount === 0) {
            $this->recordScore('6.3', 0);
            $this->assertTrue(true);

            return;
        }

        $missingCount = 7 - $presentCount;
        $deduction = min($max / 2, $missingCount * ($max / 4));
        $earned = max(0, $max - $deduction);

        $this->recordScore('6.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 6.4
     *
     * @maxPoints 0.578
     *
     * @partial Half credit if error format incorrect (e.g. flat array)
     */
    public function test_6_4_422_validation_includes_field_specific_errors(): void
    {
        $max = 0.578;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        $r = $this->safe(fn () => $this->postJson('/api/products', [
            'unit_code' => 'INVALID',
        ], $this->authHeaders($token)));

        if ($this->isServerError($r) || $r->status() !== 422) {
            $this->recordScore('6.4', 0);
            $this->assertTrue(true);

            return;
        }

        $errors = $r->json('errors');
        // Format yang benar (PDF): errors: { name: [...], unit_code: [...] }
        $isObjectFormat = is_array($errors) && isset($errors['name']) && is_array($errors['name']);

        $earned = $isObjectFormat ? $max : ($errors ? $max / 2 : 0);

        $this->recordScore('6.4', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 6.5
     *
     * @maxPoints 0.578
     *
     * @partial Half credit if status codes correct but messages inconsistent
     */
    public function test_6_5_403_and_404_with_correct_messages(): void
    {
        $max = 0.578;
        $this->seedAll();
        $token = $this->loginAs(); // budi

        $r403 = $this->safe(fn () => $this->getJson('/api/products/8', $this->authHeaders($token)));
        $r404 = $this->safe(fn () => $this->getJson('/api/products/99999', $this->authHeaders($token)));

        if ($this->isServerError($r403) || $this->isServerError($r404)) {
            $this->recordScore('6.5', 0);
            $this->assertTrue(true);

            return;
        }

        $is403 = $r403->status() === 403;
        $is404 = $r404->status() === 404;

        // PDF: "Forbidden access" dan "Not found"
        $msg403 = strtolower((string) $r403->json('message'));
        $msg404 = strtolower((string) $r404->json('message'));
        $hasForbiddenMsg = $is403 && str_contains($msg403, 'forbidden');
        $hasNotFoundMsg = $is404 && (str_contains($msg404, 'not found') || str_contains($msg404, 'not_found'));

        // Auto-404 Laravel untuk route yang belum dibuat membuat $is404
        // trivially true, jadi partial credit di-anchor ke $is403 (butuh
        // policy + ownership beneran).
        $earned = match (true) {
            $hasForbiddenMsg && $hasNotFoundMsg => $max,
            $is403 && $is404 => $max / 2,
            $is403 => $max / 4,
            default => 0,
        };

        $this->recordScore('6.5', $earned);
        $this->assertTrue(true);
    }
}
