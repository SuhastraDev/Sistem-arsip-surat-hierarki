<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class HolidayService
{
    private const SOURCE_URL = 'https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/holidays.json';

    private const CACHE_KEY = 'id-national-holidays';

    public function isHoliday(string $date): bool
    {
        return array_key_exists($date, $this->holidays());
    }

    /**
     * @return array<string, string> map of "Y-m-d" => holiday name
     */
    private function holidays(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(12), function (): array {
            try {
                $response = Http::timeout(5)->get(self::SOURCE_URL);

                if (! $response->successful()) {
                    return [];
                }

                $data = $response->json() ?? [];
                unset($data['info']);

                return collect($data)
                    ->filter(fn ($value, $key) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $key))
                    ->map(fn ($value) => $value['summary'] ?? '')
                    ->all();
            } catch (Throwable $exception) {
                Log::warning('Gagal mengambil data hari libur nasional: '.$exception->getMessage());

                return [];
            }
        });
    }
}
