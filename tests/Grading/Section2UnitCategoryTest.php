<?php

namespace Tests\Grading;

use Database\Seeders\CategorySeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Grading\Concerns\InteractsWithApi;
use Tests\Grading\Concerns\RecordsCriterionScore;
use Tests\TestCase;

/**
 * Section 2 — Unit & Category (2.88 points, 5 criteria)
 */
class Section2UnitCategoryTest extends TestCase
{
    use RefreshDatabase, InteractsWithApi, RecordsCriterionScore;

    /**
     * @criterion 2.1
     * @maxPoints 0.576
     */
    public function test_2_1_get_units_returns_200_with_array(): void
    {
        $max = 0.576;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->getJson('/api/units', $this->authHeaders($token)));
        $units = $response ? $response->json('data.units') : null;

        $ok = $response && $response->status() === 200 && is_array($units);
        $earned = $ok ? $max : 0;

        $this->recordScore('2.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 2.2
     * @maxPoints 0.576
     * @partial Half credit if 3-4 of 6 fields present
     */
    public function test_2_2_each_unit_has_required_fields(): void
    {
        $max = 0.576;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->getJson('/api/units', $this->authHeaders($token)));
        $units = $response ? $response->json('data.units') : null;

        if (!is_array($units) || empty($units)) {
            $this->recordScore('2.2', 0);
            $this->assertTrue(true);
            return;
        }

        $required = ['id', 'name', 'symbol', 'code', 'created_at', 'updated_at'];
        $first = $units[0];
        $presentCount = count(array_filter($required, fn($f) => isset($first[$f])));

        $earned = match (true) {
            $presentCount === 6 => $max,
            $presentCount >= 3 => $max / 2,
            default => 0,
        };

        $this->recordScore('2.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 2.3
     * @maxPoints 0.576
     */
    public function test_2_3_get_categories_returns_200_with_array(): void
    {
        $max = 0.576;
        $this->seedSafe([UserSeeder::class, CategorySeeder::class]);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->getJson('/api/categories', $this->authHeaders($token)));
        $categories = $response ? $response->json('data.categories') : null;

        $ok = $response && $response->status() === 200 && is_array($categories);
        $earned = $ok ? $max : 0;

        $this->recordScore('2.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 2.4
     * @maxPoints 0.576
     * @partial Half credit if 4-5 of 6 fields present
     */
    public function test_2_4_each_category_has_required_fields(): void
    {
        $max = 0.576;
        $this->seedSafe([UserSeeder::class, CategorySeeder::class]);
        $token = $this->loginAs();

        $response = $this->safe(fn() => $this->getJson('/api/categories', $this->authHeaders($token)));
        $categories = $response ? $response->json('data.categories') : null;

        if (!is_array($categories) || empty($categories)) {
            $this->recordScore('2.4', 0);
            $this->assertTrue(true);
            return;
        }

        $required = ['id', 'name', 'icon', 'color', 'type', 'created_at'];
        $first = $categories[0];
        $presentCount = count(array_filter($required, fn($f) => isset($first[$f])));
        $hasTypeInOut = isset($first['type']) && in_array($first['type'], ['IN', 'OUT'], true);

        $earned = match (true) {
            $presentCount === 6 && $hasTypeInOut => $max,
            $presentCount >= 4 => $max / 2,
            default => 0,
        };

        $this->recordScore('2.4', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 2.5
     * @maxPoints 0.576
     * @partial Half credit if only one endpoint properly protected
     */
    public function test_2_5_both_endpoints_require_bearer_token(): void
    {
        $max = 0.576;

        $r1 = $this->safe(fn() => $this->getJson('/api/units', $this->invalidAuthHeaders()));
        $r2 = $this->safe(fn() => $this->getJson('/api/categories', $this->invalidAuthHeaders()));

        $units401 = $r1 && $r1->status() === 401;
        $cats401 = $r2 && $r2->status() === 401;

        $earned = match (true) {
            $units401 && $cats401 => $max,
            $units401 || $cats401 => $max / 2,
            default => 0,
        };

        $this->recordScore('2.5', $earned);
        $this->assertTrue(true);
    }
}
