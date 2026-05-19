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
 *
 * Response shape acuan (json-response.pdf):
 *   A1a Register 201: { status, message, data:{ id, name, email, created_at, updated_at, token } }
 *   A1b 422:          { status:"error", message:"Invalid field", errors:{ name:[], email:[], password:[] } }
 *   A2a Login 200:    { status, message, data:{ id, name, email, created_at, updated_at, token } }
 *   A2b Login 401:    { status:"error", message:"Username or password incorrect" }
 *   A3a Logout 200:   { status, message:"Logout successful" }
 *   A3b Logout 401:   { status:"error", message:"Unauthenticated." }
 */
class Section1AuthTest extends TestCase
{
    use InteractsWithApi, RecordsCriterionScore, RefreshDatabase;

    /**
     * @criterion 1.1
     *
     * @maxPoints 0.577
     *
     * @partial Half credit if endpoint exists but returns wrong status
     */
    public function test_1_1_register_creates_user_returns_201(): void
    {
        $max = 0.577;
        $response = $this->safe(fn () => $this->postJson('/api/auth/register', [
            'full_name' => 'Test User',
            'email' => 'newuser.1.1@webtech.id',
            'password' => 'secret123',
        ]));

        if ($this->isServerError($response)) {
            $this->recordScore('1.1', 0);
            $this->assertTrue(true);

            return;
        }

        $status = $response->status();
        $earned = match (true) {
            $status === 201 => $max,
            in_array($status, [200, 422], true) => $max / 2,
            default => 0,
        };

        $this->recordScore('1.1', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.2
     *
     * @maxPoints 0.577
     *
     * @partial Half credit if user data returned but token missing
     */
    public function test_1_2_register_response_includes_user_data_and_token(): void
    {
        $max = 0.577;
        $response = $this->safe(fn () => $this->postJson('/api/auth/register', [
            'full_name' => 'Test User',
            'email' => 'newuser.1.2@webtech.id',
            'password' => 'secret123',
        ]));

        if ($this->isServerError($response)) {
            $this->recordScore('1.2', 0);
            $this->assertTrue(true);

            return;
        }

        $status = $response->status();
        $data = $response->json('data');
        $hasUserData = is_array($data)
            && isset($data['id'], $data['name'], $data['email'], $data['created_at'], $data['updated_at']);
        $hasToken = is_array($data) && ! empty($data['token']);

        // Hanya berikan kredit kalau status memang menandakan create berhasil.
        if (! in_array($status, [200, 201], true)) {
            $earned = 0;
        } else {
            $earned = ($hasUserData && $hasToken) ? $max : ($hasUserData ? $max / 2 : 0);
        }

        $this->recordScore('1.2', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.3
     *
     * @maxPoints 0.577
     *
     * @partial Half credit if some fields validated but error format inconsistent
     */
    public function test_1_3_register_validates_required_fields(): void
    {
        $max = 0.577;
        $response = $this->safe(fn () => $this->postJson('/api/auth/register', []));

        if ($this->isServerError($response)) {
            $this->recordScore('1.3', 0);
            $this->assertTrue(true);

            return;
        }

        $status = $response->status();
        $errors = $response->json('errors');

        // Pastikan 'errors' adalah objek per-field (sesuai PDF), bukan flat array.
        $hasAllFields = is_array($errors)
            && isset($errors['full_name'], $errors['email'], $errors['password']);
        $someField = is_array($errors)
            && (isset($errors['full_name']) || isset($errors['email']) || isset($errors['password']));

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
     *
     * @maxPoints 0.288
     *
     * @partial Half credit if format-only or uniqueness-only
     */
    public function test_1_4_register_validates_email_format_and_uniqueness(): void
    {
        $max = 0.288;
        $this->seedSafe(UserSeeder::class);

        $r1 = $this->safe(fn () => $this->postJson('/api/auth/register', [
            'full_name' => 'X', 'email' => 'not-an-email', 'password' => 'secret123',
        ]));
        $r2 = $this->safe(fn () => $this->postJson('/api/auth/register', [
            'full_name' => 'X', 'email' => 'budi@webtech.id', 'password' => 'secret123',
        ]));

        if ($this->isServerError($r1) || $this->isServerError($r2)) {
            $this->recordScore('1.4', 0);
            $this->assertTrue(true);

            return;
        }

        $formatOk = $r1->status() === 422 && $r1->json('errors.email');
        $uniqueOk = $r2->status() === 422 && $r2->json('errors.email');

        $passed = (int) (bool) $formatOk + (int) (bool) $uniqueOk;
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
     *
     * @maxPoints 0.288
     *
     * @partial No
     */
    public function test_1_5_register_validates_password_min_6(): void
    {
        $max = 0.288;
        $response = $this->safe(fn () => $this->postJson('/api/auth/register', [
            'full_name' => 'X', 'email' => 'minpw@webtech.id', 'password' => 'abc',
        ]));

        if ($this->isServerError($response)) {
            $this->recordScore('1.5', 0);
            $this->assertTrue(true);

            return;
        }

        $ok = $response->status() === 422 && $response->json('errors.password');
        $earned = $ok ? $max : 0;

        $this->recordScore('1.5', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.6
     *
     * @maxPoints 0.577
     *
     * @partial Half credit if login works but token missing
     */
    public function test_1_6_login_authenticates_returns_token(): void
    {
        $max = 0.577;
        $this->seedSafe(UserSeeder::class);

        $response = $this->safe(fn () => $this->postJson('/api/auth/login', [
            'email' => 'budi@webtech.id', 'password' => 'password',
        ]));

        if ($this->isServerError($response)) {
            $this->recordScore('1.6', 0);
            $this->assertTrue(true);

            return;
        }

        $status = $response->status();
        $token = $response->json('data.token');

        $earned = match (true) {
            $status === 200 && ! empty($token) => $max,
            $status === 200 => $max / 2,
            default => 0,
        };

        $this->recordScore('1.6', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.7
     *
     * @maxPoints 0.288
     *
     * @partial Half credit if returns error but wrong status code
     */
    public function test_1_7_login_returns_401_on_wrong_credentials(): void
    {
        $max = 0.288;
        $this->seedSafe(UserSeeder::class);

        $response = $this->safe(fn () => $this->postJson('/api/auth/login', [
            'email' => 'budi@webtech.id', 'password' => 'wrong-password',
        ]));

        if ($this->isServerError($response)) {
            $this->recordScore('1.7', 0);
            $this->assertTrue(true);

            return;
        }

        $status = $response->status();
        $earned = match (true) {
            $status === 401 => $max,
            in_array($status, [400, 403, 422], true) => $max / 2,
            default => 0,
        };

        $this->recordScore('1.7', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.8
     *
     * @maxPoints 0.865
     *
     * @partial Half credit if logout works but revokes ALL tokens instead of current
     */
    public function test_1_8_logout_deactivates_current_device_token_only(): void
    {
        $max = 0.865;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);

        $token1 = $this->loginAs();
        $token2 = $this->loginAs();

        if (! $token1 || ! $token2) {
            $this->recordScore('1.8', 0);
            $this->assertTrue(true);

            return;
        }

        $logout = $this->safe(fn () => $this->postJson('/api/auth/logout', [], $this->authHeaders($token1)));
        $r1 = $this->safe(fn () => $this->getJson('/api/units', $this->authHeaders($token1)));
        $r2 = $this->safe(fn () => $this->getJson('/api/units', $this->authHeaders($token2)));

        if ($this->isServerError($logout) || $this->isServerError($r1) || $this->isServerError($r2)) {
            $this->recordScore('1.8', 0);
            $this->assertTrue(true);

            return;
        }

        $logoutOk = $logout->status() === 200;
        $token1Invalid = $r1->status() === 401;
        $token2StillValid = $r2->status() === 200;

        $earned = match (true) {
            $logoutOk && $token1Invalid && $token2StillValid => $max,
            $logoutOk && $token1Invalid => $max / 2,
            $logoutOk => $max / 4,
            default => 0,
        };

        $this->recordScore('1.8', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.9
     *
     * @maxPoints 0.288
     *
     * @partial —
     */
    public function test_1_9_logout_returns_200_with_success_message(): void
    {
        $max = 0.288;
        $this->seedSafe(UserSeeder::class);
        $token = $this->loginAs();

        if (! $token) {
            $this->recordScore('1.9', 0);
            $this->assertTrue(true);

            return;
        }

        $response = $this->safe(fn () => $this->postJson('/api/auth/logout', [], $this->authHeaders($token)));

        if ($this->isServerError($response)) {
            $this->recordScore('1.9', 0);
            $this->assertTrue(true);

            return;
        }

        $ok = $response->status() === 200 && ! empty($response->json('message'));
        $earned = $ok ? $max : 0;

        $this->recordScore('1.9', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.10
     *
     * @maxPoints 0.577
     *
     * @partial Half credit if token works in some endpoints but not all
     */
    public function test_1_10_sanctum_token_works_as_bearer(): void
    {
        $max = 0.577;
        $this->seedSafe([UserSeeder::class, UnitSeeder::class]);
        $token = $this->loginAs();

        if (! $token) {
            $this->recordScore('1.10', 0);
            $this->assertTrue(true);

            return;
        }

        $endpoints = ['/api/units', '/api/categories', '/api/products'];
        $passCount = 0;
        $anyServerError = false;
        foreach ($endpoints as $url) {
            $r = $this->safe(fn () => $this->getJson($url, $this->authHeaders($token)));
            if ($this->isServerError($r)) {
                $anyServerError = true;

                continue;
            }
            if ($r->status() === 200) {
                $passCount++;
            }
        }

        // Kalau salah satu endpoint 5xx, jangan beri kredit penuh.
        $earned = match (true) {
            $passCount === 3 && ! $anyServerError => $max,
            $passCount >= 1 => $max / 2,
            default => 0,
        };

        $this->recordScore('1.10', $earned);
        $this->assertTrue(true);
    }

    /**
     * @criterion 1.11
     *
     * @maxPoints 0.288
     *
     * @partial —
     */
    public function test_1_11_protected_endpoints_reject_invalid_token(): void
    {
        $max = 0.288;

        $r1 = $this->safe(fn () => $this->getJson('/api/units', $this->invalidAuthHeaders()));
        $r2 = $this->safe(fn () => $this->getJson('/api/products', $this->invalidAuthHeaders()));

        if ($this->isServerError($r1) || $this->isServerError($r2)) {
            $this->recordScore('1.11', 0);
            $this->assertTrue(true);

            return;
        }

        $u401 = $r1->status() === 401;
        $p401 = $r2->status() === 401;

        $earned = match (true) {
            $u401 && $p401 => $max,
            $u401 || $p401 => $max / 2,
            default => 0,
        };

        $this->recordScore('1.11', $earned);
        $this->assertTrue(true);
    }
}
