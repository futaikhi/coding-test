<?php

namespace App\Jobs;

use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

class ImportCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected array $mapping;
    protected string $userId;

    public function __construct(string $filePath, array $mapping, string $userId)
    {
        $this->filePath = $filePath;
        $this->mapping = $mapping;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $path = Storage::disk('local')->path($this->filePath);
        $totalRows = count(file($path)) - 1;
        if ($totalRows <= 0)
            return;

        $file = fopen($path, 'r');
        $headers = fgetcsv($file); // Skip header

        $processed = 0;
        $errors = [];

        while (($row = fgetcsv($file)) !== false) {
            $processed++;

            try {
                // Konversi index mapping secara presisi (Aman untuk indeks 0)
                $nameIdx = isset($this->mapping['name']) ? (int) $this->mapping['name'] : null;
                $activeIdx = isset($this->mapping['is_active']) ? (int) $this->mapping['is_active'] : null;
                $rackIdx = isset($this->mapping['rack']) ? (int) $this->mapping['rack'] : null;
                $tempIdx = isset($this->mapping['temperature']) ? (int) $this->mapping['temperature'] : null;

                $name = ($nameIdx !== null && isset($row[$nameIdx])) ? trim($row[$nameIdx]) : null;
                $activeStr = ($activeIdx !== null && isset($row[$activeIdx])) ? strtolower(trim($row[$activeIdx])) : 'aktif';
                $rack = ($rackIdx !== null && isset($row[$rackIdx])) ? trim($row[$rackIdx]) : '';
                $temp = ($tempIdx !== null && isset($row[$tempIdx])) ? trim($row[$tempIdx]) : '';

                if ($name === null || $name === '') {
                    $errors[] = "Baris {$processed}: Nama kategori tidak boleh kosong.";
                    continue;
                }

                // Cek Duplikasi Nama Kategori
                if (Category::where('name', $name)->exists()) {
                    $errors[] = "Baris {$processed}: Kategori '{$name}' sudah terdaftar (Gagal Duplikasi).";
                    continue;
                }

                // HANDLING DATA BOOLEAN
                $isActive = in_array($activeStr, ['aktif', '1', 'true', 'yes', 'y']);

                // HANDLING & MERAKIT DATA JSON METADATA
                $metadataArray = [
                    'rack' => $rack !== '' ? $rack : 'Belum Diatur',
                    'temperature' => $temp !== '' ? $temp : 'Normal'
                ];

                Category::create([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'is_active' => $isActive,
                    'attributes' => $metadataArray // Otomatis ter-cast ke JSON text oleh model
                ]);

            } catch (\Exception $e) {
                $errors[] = "Baris {$processed}: Error sistem (" . $e->getMessage() . ")";
            }

            // Update live progress cache
            Cache::put("import_progress_cat_{$this->userId}", [
                'status' => 'processing',
                'current' => $processed,
                'total' => $totalRows,
                'percentage' => round(($processed / $totalRows) * 100),
                'errors' => $errors
            ], 600);
        }

        fclose($file);
        Storage::disk('local')->delete($this->filePath);

        Cache::put("import_progress_cat_{$this->userId}", [
            'status' => 'completed',
            'current' => $processed,
            'total' => $totalRows,
            'percentage' => 100,
            'errors' => $errors
        ], 600);

        $successCount = $processed - count($errors);

        Audit::create([
            'user_type' => 'App\Models\User',
            'user_id' => $this->userId,
            'event' => 'imported', // Nama event kustom
            'auditable_type' => 'App\Models\Category',
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'old_values' => [],
            'new_values' => [
                'summary' => "Total: {$processed} baris (Sukses: {$successCount}, Gagal: " . count($errors) . ")"
            ],
            'url' => '/categories',
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => 'Queue Worker'
        ]);
    }
}