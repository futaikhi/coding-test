<?php

namespace App\Jobs;

use App\Models\Material;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImportMaterialsJob implements ShouldQueue
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
        
        // 1. Hitung total baris untuk kalkulasi persentase progress bar
        $totalRows = count(file($path)) - 1; 
        if ($totalRows <= 0) return;

        $file = fopen($path, 'r');
        
        // Baca baris pertama (header) untuk mendapatkan indeks array index kolom
        $headers = fgetcsv($file);

        $processed = 0;
        $errors = [];

        // 2. Loop membaca data baris demi baris
        while (($row = fgetcsv($file)) !== false) {
            $processed++;

            try {
                // Ambil data berdasarkan indeks kolom hasil mapping dari Vue
                $name = $row[$this->mapping['name']] ?? null;
                $categoryName = $row[$this->mapping['category']] ?? null;
                $publishedAt = $row[$this->mapping['published_at']] ?? null;
                $description = $row[$this->mapping['description']] ?? null;

                // Validasi data minimal wajib di level background
                if (empty($name)) {
                    $errors[] = "Baris {$processed}: Nama material tidak boleh kosong.";
                    continue;
                }

                // Cari kategori berdasarkan nama yang diketik di CSV, jika tidak ada arahkan ke default/buat baru
                $category = Category::where('name', 'like', '%' . $categoryName . '%')->first();
                if (!$category) {
                    $errors[] = "Baris {$processed}: Kategori '{$categoryName}' tidak ditemukan di sistem database.";
                    continue;
                }

                // Generate kode material unik aman secara backend seperti regulasi topik 2
                $year = date('Y');
                do {
                    $randomDigits = rand(1000, 9999);
                    $generatedCode = "MAT-{$year}-{$randomDigits}";
                } while (Material::where('code', $generatedCode)->exists());

                // Simpan record data ke database
                Material::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'category_id' => $category->id,
                    'code' => $generatedCode,
                    'name' => $name,
                    'description' => $description,
                    'published_at' => $publishedAt ? date('Y-m-d H:i:s', strtotime($publishedAt)) : now(),
                ]);

            } catch (\Exception $e) {
                $errors[] = "Baris {$processed}: Terjadi kesalahan sistem (" . $e->getMessage() . ")";
            }

            // 3. Update Progress state ke dalam Cache secara berkala agar Vue bisa memantau
            Cache::put("import_progress_{$this->userId}", [
                'status'    => 'processing',
                'current'   => $processed,
                'total'     => $totalRows,
                'percentage'=> round(($processed / $totalRows) * 100),
                'errors'    => $errors
            ], 600); // Bertahan selama 10 menit
        }

        fclose($file);
        
        // Hapus file sementara dari storage local server setelah selesai
        Storage::disk('local')->delete($this->filePath);

        // 4. Tandai bahwa status background proses telah Selesai
        Cache::put("import_progress_{$this->userId}", [
            'status'    => 'completed',
            'current'   => $processed,
            'total'     => $totalRows,
            'percentage'=> 100,
            'errors'    => $errors
        ], 600);
    }
}