<?php

namespace Tests\Grading;

use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Grading\Concerns\InteractsWithApi;
use Tests\Grading\Concerns\RecordsCriterionScore;
use Tests\TestCase;

/**
 * Section 1 — Authentication (5.19 points, 11 criteria)
 */
class Section1AuthTest extends TestCase
{
    use RefreshDatabase, InteractsWithApi, RecordsCriterionScore;

    /**
     * @criterion 1.1
     * @maxPoints 0.577
     * @partial Half credit if endpoint exists but returns wrong status
     */
    public function test_1_1_register_creates_user_returns_201(): void
    {
        $max = 0.577;
        $response = $this->safe(fn() => $this->postJson('/api/auth/register', [
            'full_name' => 'Test User',
            'email' => 'newuser.1.1@webtech.id',
            'password' => 'secret123',
        ]));

        $status = $response ? $response->status() : 0;
        $earned = match (true) {
            $status === 201 => $max,
            in_array($status, [200, 422]) => $max / 2,
            default => 0,
        };

        $this->recordScore('1.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.2
     * @maxPoints 0.577
     * @partial Half credit if user data returned but token missing
     */
    public function test_1_2_register_response_includes_user_data_and_token(): void
    {
        $max = 0.577;
        $response = $this->safe(fn() => $this->postJson('/api/auth/register', [
            'full_name' => 'Test User',
            'email' => 'newuser.1.2@webtech.id',
            'password' => 'secret123',
        ]));

        $data = $response ? $response->json('data') : null;
        $hasUserData = $data && isset($data['id'], $data['name'], $data['email'], $data['created_at'], $data['updated_at']);
        $hasToken = $data && !empty($data['token']);

        $earned = ($hasUserData && $hasToken) ? $max : ($hasUserData ? $max / 2 : 0);

        $this->recordScore('1.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.3
     * @maxPoints 0.577
     * @partial Half credit if some fields validated but error format inconsistent
     */
    public function test_1_3_register_validates_required_fields(): void
    {
        $max = 0.577;
        $response = $this->safe(fn() => $this->postJson('/api/auth/register', []));

        $status = $response ? $response->status() : 0;
        $errors = $response ? $response->json('errors') : null;

        $hasAllFields = $errors && isset($errors['full_name'], $errors['email'], $errors['password']);
        $someField = $errors && (isset($errors['full_name']) || isset($errors['email']) || isset($errors['password']));

        $earned = match (true) {
            $status === 422 && $hasAllFields => $max,
            $status === 422 && $someField => $max / 2,
            default => 0,
        };

        $this->recordScore('1.3', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.4
     * @maxPoints 0.288
     * @partial Half credit if format-only or uniqueness-only
     */
    public function test_1_4_register_validates_email_format_and_uniqueness(): void
    {
        $max = 0.288;
        $this->seedSafe(UserSeeder::class);

        // Sub-check 1: Email format validation
        $r1 = $this->safe(fn() => $this->postJson('/api/auth/register', [
            'full_name' => 'X', 'email' => 'not-an-email', 'password' => 'secret123',
        ]));
        $formatOk = $r1 && $r1->status() === 422 && $r1->json('errors.email');

        // Sub-check 2: Email uniqueness
        $r2 = $this->safe(fn() => $this->postJson('/api/auth/register', [
            'full_name' => 'X', 'email' => 'budi@webtech.id', 'password' => 'secret123',
        ]));
        $uniqueOk = $r2 && $r2->status() === 422 && $r2->json('errors.email');

        $passed = (int) $formatOk + (int) $uniqueOk;
        $earned = match ($passed) {
            2 => $max,
            1 => $max / 2,
            default => 0,
        };

        $this->recordScore('1.4', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.5
     * @maxPoints 0.288
     * @partial No
     */
    public function test_1_5_register_validates_password_min_6(): void
    {
        $max = 0.288;
        $response = $this->safe(fn() => $this->postJson('/api/auth/register', [
            'full_name' => 'X', 'email' => 'minpw@webtech.id', 'password' => 'abc',
        ]));

        $ok = $response && $response->status() === 422 && $response->json('errors.password');
        $earned = $ok ? $max : 0;

        $this->recordScore('1.5', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.6
     * @maxPoints 0.577
     * @partial Half credit if login works but token missing
     */
    public function test_1_6_login_authenticates_returns_token(): void
    {
        $max = 0.577;
        $this->seedSafe(UserSeeder::class);

        $response = $this->safe(fn() => $this->postJson('/api/auth/login', [
            'email' => 'budi@webtech.id', 'password' => 'password',
        ]));

        $status = $response ? $response->status() : 0;
        $token = $response ? $response->json('data.token') : null;

        $earned = match (true) {
            $status === 200 && !empty($token) => $max,
            $status === 200 => $max / 2,
            default => 0,
        };

        $this->recordScore('1.6', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.7
     * @maxPoints 0.288
     * @partial Half credit if returns error but wrong status code
     */
    public function test_1_7_login_returns_401_on_wrong_credentials(): void
    {
        $max = 0.288;
        $this->seedSafe(UserSeeder::class);

        $response = $this->safe(fn() => $this->postJson('/api/auth/login', [
            'email' => 'budi@webtech.id', 'password' => 'wrong-password',
        ]));

        $status = $response ? $response->status() : 0;
        $earned = match (true) {
            $status === 401 => $max,
            in_array($status, [400, 403, 422]) => $max / 2,
            default => 0,
        };

        $this->recordScore('1.7', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.8
     * @maxPoints 0.865
     * @partial Half credit if logout works but revokes ALL tokens instead of current
     */
    public function test_1_8_logout_deactivates_current_device_token_only(): void
    {
        $max = 0.865;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);

        // Login dari 2 "device" → 2 token berbeda
        $token1 = $this->loginAs();
        $token2 = $this->loginAs();

        if (!$token1 || !$token2) {
            $this->recordScore('1.8', 0);
            $this->assertTrue(true);
            return;
        }

        // Logout pakai token1
        $logout = $this->safe(fn() => $this->postJson('/api/auth/logout', [], $this->authHeaders($token1)));
        $logoutOk = $logout && $logout->status() === 200;

        // Token1 harus invalid sekarang (401)
        $r1 = $this->safe(fn() => $this->getJson('/api/units', $this->authHeaders($token1)));
        $token1Invalid = $r1 && $r1->status() === 401;

        // Token2 HARUS tetap valid (200) — karena logout hanya per-device
        $r2 = $this->safe(fn() => $this->getJson('/api/units', $this->authHeaders($token2)));
        $token2StillValid = $r2 && $r2->status() === 200;

        $earned = match (true) {
            $logoutOk && $token1Invalid && $token2StillValid => $max,
            $logoutOk && $token1Invalid => $max / 2, // revoked all tokens
            $logoutOk => $max / 4,
            default => 0,
        };

        $this->recordScore('1.8', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.9
     * @maxPoints 0.288
     * @partial —
     */
    public function test_1_9_logout_returns_200_with_success_message(): void
    {
        $max = 0.288;
        $this->seedSafe(UserSeeder::class);
        $token = $this->loginAs();

        if (!$token) {
            $this->recordScore('1.9', 0);
            $this->assertTrue(true);
            return;
        }

        $response = $this->safe(fn() => $this->postJson('/api/auth/logout', [], $this->authHeaders($token)));

        $ok = $response
            && $response->status() === 200
            && in_array($response->json('status'), ['success', null], true)
            && !empty($response->json('message'));

        $earned = $ok ? $max : 0;

        $this->recordScore('1.9', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.10
     * @maxPoints 0.577
     * @partial Half credit if token works in some endpoints but not all
     */
    public function test_1_10_sanctum_token_works_as_bearer(): void
    {
        $max = 0.577;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        if (!$token) {
            $this->recordScore('1.10', 0);
            $this->assertTrue(true);
            return;
        }

        // Test token di 3 endpoint berbeda
        $endpoints = ['/api/units', '/api/categories', '/api/products'];
        $passCount = 0;
        foreach ($endpoints as $url) {
            $r = $this->safe(fn() => $this->getJson($url, $this->authHeaders($token)));
            if ($r && $r->status() === 200) {
                $passCount++;
            }
        }

        $earned = match (true) {
            $passCount === 3 => $max,
            $passCount >= 1 => $max / 2,
            default => 0,
        };

        $this->recordScore('1.10', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.11
     * @maxPoints 0.288
     * @partial —
     */
    public function test_1_11_protected_endpoints_reject_invalid_token(): void
    {
        $max = 0.288;

        $r1 = $this->safe(fn() => $this->getJson('/api/units', $this->invalidAuthHeaders()));
        $r2 = $this->safe(fn() => $this->getJson('/api/products', $this->invalidAuthHeaders()));

        $ok = $r1 && $r1->status() === 401
            && $r2 && $r2->status() === 401;

        $earned = $ok ? $max : ($r1 && $r1->status() === 401 ? $max / 2 : 0);

        $this->recordScore('1.11', $earned);
        $this->assertTrue(true);
    }
}
