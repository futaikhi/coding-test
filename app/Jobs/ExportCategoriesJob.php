<?php

namespace App\Jobs;

use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Models\Audit;

class ExportCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $filters;
    protected string $userId;

    public function __construct(array $filters, string $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $query = Category::query();

        // Filter: Search nama kategori
        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', '%' . $this->filters['search'] . '%');
        }

        $categories = $query->latest()->get();
        $fileName = 'exports/categories_export_' . $this->userId . '.csv';

        if (!Storage::disk('public')->exists('exports')) {
            Storage::disk('public')->makeDirectory('exports');
        }

        $filePath = Storage::disk('public')->path($fileName);
        $file = fopen($filePath, 'w');

        // UTF-8 BOM untuk Excel
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header: Memisahkan field JSON attributes menjadi kolom tersendiri
        fputcsv($file, ['Nama Kategori', 'Status Aktif', 'Lokasi Rak (JSON)', 'Suhu Ruangan (JSON)']);

        foreach ($categories as $cat) {
            fputcsv($file, [
                $cat->name,
                $cat->is_active ? 'Aktif' : 'Non-Aktif', // Casting Boolean ke Text
                $cat->attributes['rack'] ?? '-',            // Ekstrak JSON Key 1
                $cat->attributes['temperature'] ?? '-'     // Ekstrak JSON Key 2
            ]);
        }

        fclose($file);

        Audit::create([
            'user_type' => 'App\Models\User',
            'user_id' => $this->userId,
            'event' => 'exported', // Nama event kustom
            'auditable_type' => 'App\Models\Category', // Kolom target modul
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'old_values' => [],
            'new_values' => ['info' => 'Mengekspor data ke berkas CSV/Excel berfilter.'],
            'url' => '/categories',
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => 'Queue Worker'
        ]);
    }
}