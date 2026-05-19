<?php

namespace Tests\Grading\Concerns;

trait RecordsCriterionScore
{
    /**
     * In-memory scores untuk test run ini.
     * Di-persist ke disk setiap recordScore() supaya partial run tetap ada hasilnya.
     */
    protected static array $scores = [];

    protected function recordScore(string $criterionId, float $earned): void
    {
        $path = storage_path('grading-scores.json');

        // Static $scores adalah per-class. Supaya hasil semua section tertulis di file,
        // gabungkan dengan isi file yang sudah ada sebelum menulis ulang.
        $existing = [];
        if (file_exists($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $existing[$criterionId] = round($earned, 3);
        self::$scores = $existing;

        @file_put_contents(
            $path,
            json_encode(self::$scores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function persistScores(): void
    {
        $path = storage_path('grading-scores.json');
        @file_put_contents(
            $path,
            json_encode(self::$scores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Reset scores file di awal test run (panggil di setUpBeforeClass yang paling awal,
     * atau lewat env). Untuk kemudahan: reset hanya saat scores array kosong.
     */
    protected function resetScoresIfFresh(): void
    {
        if (empty(self::$scores)) {
            $path = storage_path('grading-scores.json');
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }
}
