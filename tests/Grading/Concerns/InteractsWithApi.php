<?php

namespace Tests\Grading\Concerns;

trait InteractsWithApi
{
    /**
     * Login via API dan return plain text token.
     * Default pakai user seeder: budi@webtech.id / password.
     */
    protected function loginAs(string $email = 'budi@webtech.id', string $password = 'password'): ?string
    {
        try {
            $response = $this->postJson('/api/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);
            return $response->json('data.token');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Header authorization untuk request dengan token.
     */
    protected function authHeaders(?string $token): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . ($token ?? 'no-token'),
        ];
    }

    /**
     * Header dengan token invalid (untuk test 401).
     */
    protected function invalidAuthHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer this-is-an-invalid-token-12345',
        ];
    }

    /**
     * Safely call a JSON endpoint and never throw — return null on exception.
     */
    protected function safe(callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Safely call seed() and return bool — false if migration doesn't exist yet
     * (so the test can gracefully record 0 score and skip rest of assertions).
     */
    protected function seedSafe($class): bool
    {
        try {
            $this->seed($class);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
