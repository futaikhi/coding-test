<?php

namespace App\Jobs;

use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportMaterialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $filters;
    protected string $userId;

    // REVISI: Tangkap data filter dan ID User dari Controller
    public function __construct(array $filters, string $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        // 1. Bangun Query Builder secara dinamis berdasarkan properti $this->filters
        $query = Material::with('category');

        // Filter: Search kata kunci
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        // Filter: Kategori barang
        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        // Sorting Kolom Dinamis
        $sortBy = $this->filters['sort_by'] ?? 'created_at';
        $sortOrder = $this->filters['sort_order'] ?? 'desc';
        
        if (in_array($sortBy, ['name', 'code', 'published_at', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Ambil hasil data yang sudah ter-filter aman
        $materials = $query->get();

        // 2. Tentukan nama file unik per user agar tidak bentrok di server
        $fileName = 'exports/materials_export_' . $this->userId . '.csv';
        
        if (!Storage::disk('public')->exists('exports')) {
            Storage::disk('public')->makeDirectory('exports');
        }

        $filePath = Storage::disk('public')->path($fileName);
        $file = fopen($filePath, 'w');

        // Inject UTF-8 BOM untuk Excel Compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Tulis Header CSV
        fputcsv($file, ['Kode Material', 'Nama Material', 'Kategori', 'Tanggal Rilis', 'Deskripsi']);

        // Tulis isi baris data
        foreach ($materials as $material) {
            fputcsv($file, [
                $material->code,
                $material->name,
                $material->category ? $material->category->name : 'Tanpa Kategori',
                $material->published_at ? $material->published_at->format('Y-m-d') : '-',
                $material->description ?? '-'
            ]);
        }

        fclose($file);
    }
}