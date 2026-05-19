<?php

namespace Tests\Grading;

/**
 * Section 2 — Unit & Category (2.88 points, 5 criteria)
 *
 * Baseline data dari GradingTestCase: 10 units + 14 categories sudah ter-seed.
 *
 * Response shape acuan (json-response.pdf):
 *   B1a GET /api/units 200:      { data:{ units:[ { id, name, symbol, code, created_at, updated_at } ] } }
 *   B2a GET /api/categories 200: { data:{ categories:[ { id, name, icon, color, type, created_at, updated_at } ] } }
 *   B1b/B2b 401:                 { status:"error", message:"Unauthenticated." }
 */
class Section2UnitCategoryTest extends GradingTestCase
{
    /**
     * @criterion 2.1
     *
     * @maxPoints 0.576
     */
    public function test_2_1_get_units_returns_200_with_array(): void
    {
        $max = 0.576;
        $token = $this->loginAs();

        $response = $this->safe(fn () => $this->getJson('/api/units', $this->authHeaders($token)));

        if ($this->isServerError($response)) {
            $this->recordScore('2.1', 0);
            $this->assertTrue(true);

            return;
        }

        $units = $response->json('data.units');
        $ok = $response->status() === 200 && is_array($units);
        $earned = $ok ? $max : 0;

        $this->recordScore('2.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 2.2
     *
     * @maxPoints 0.576
     *
     * @partial Half credit if 3-4 of 6 fields present
     */
    public function test_2_2_each_unit_has_required_fields(): void
    {
        $max = 0.576;
        $token = $this->loginAs();

        $response = $this->safe(fn () => $this->getJson('/api/units', $this->authHeaders($token)));

        if ($this->isServerError($response) || $response->status() !== 200) {
            $this->recordScore('2.2', 0);
            $this->assertTrue(true);

            return;
        }

        $units = $response->json('data.units');
        if (! is_array($units) || empty($units)) {
            $this->recordScore('2.2', 0);
            $this->assertTrue(true);

            return;
        }

        $required = ['id', 'name', 'symbol', 'code', 'created_at', 'updated_at'];
        $first = $units[0];
        $presentCount = is_array($first)
            ? count(array_filter($required, fn ($f) => isset($first[$f])))
            : 0;

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
     *
     * @maxPoints 0.576
     */
    public function test_2_3_get_categories_returns_200_with_array(): void
    {
        $max = 0.576;
        $token = $this->loginAs();

        $response = $this->safe(fn () => $this->getJson('/api/categories', $this->authHeaders($token)));

        if ($this->isServerError($response)) {
            $this->recordScore('2.3', 0);
            $this->assertTrue(true);

            return;
        }

        $categories = $response->json('data.categories');
        $ok = $response->status() === 200 && is_array($categories);
        $earned = $ok ? $max : 0;

        $this->recordScore('2.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 2.4
     *
     * @maxPoints 0.576
     *
     * @partial Half credit if 4-5 of 6 fields present
     */
    public function test_2_4_each_category_has_required_fields(): void
    {
        $max = 0.576;
        $token = $this->loginAs();

        $response = $this->safe(fn () => $this->getJson('/api/categories', $this->authHeaders($token)));

        if ($this->isServerError($response) || $response->status() !== 200) {
            $this->recordScore('2.4', 0);
            $this->assertTrue(true);

            return;
        }

        $categories = $response->json('data.categories');
        if (! is_array($categories) || empty($categories)) {
            $this->recordScore('2.4', 0);
            $this->assertTrue(true);

            return;
        }

        // PDF: id, name, icon, color, type, created_at, updated_at
        $required = ['id', 'name', 'icon', 'color', 'type', 'created_at'];
        $first = $categories[0];
        if (! is_array($first)) {
            $this->recordScore('2.4', 0);
            $this->assertTrue(true);

            return;
        }

        $presentCount = count(array_filter($required, fn ($f) => isset($first[$f])));
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
     *
     * @maxPoints 0.576
     *
     * @partial Half credit if only one endpoint properly protected
     */
    public function test_2_5_both_endpoints_require_bearer_token(): void
    {
        $max = 0.576;

        $r1 = $this->safe(fn () => $this->getJson('/api/units', $this->invalidAuthHeaders()));
        $r2 = $this->safe(fn () => $this->getJson('/api/categories', $this->invalidAuthHeaders()));

        if ($this->isServerError($r1) || $this->isServerError($r2)) {
            $this->recordScore('2.5', 0);
            $this->assertTrue(true);

            return;
        }

        $u401 = $r1->status() === 401;
        $c401 = $r2->status() === 401;

        $earned = match (true) {
            $u401 && $c401 => $max,
            $u401 || $c401 => $max / 2,
            default => 0,
        };

        $this->recordScore('2.5', $earned);
        $this->assertTrue(true);
    }
}
