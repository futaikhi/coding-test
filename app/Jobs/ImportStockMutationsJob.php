<?php

namespace App\Jobs;

use App\Models\StockMutation;
use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportStockMutationsJob implements ShouldQueue
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
        if ($totalRows <= 0) return;

        $file = fopen($path, 'r');
        $headers = fgetcsv($file); // Skip header baris 1

        $processed = 0;
        $errors = [];

        while (($row = fgetcsv($file)) !== false) {
            $processed++;

            try {
                // Konversi index mapping (Aman dari bug index 0)
                $matIdentifierIdx = isset($this->mapping['material_identifier']) ? (int)$this->mapping['material_identifier'] : null;
                $typeIdx          = isset($this->mapping['type']) ? (int)$this->mapping['type'] : null;
                $quantityIdx      = isset($this->mapping['quantity']) ? (int)$this->mapping['quantity'] : null;
                $noteIdx          = isset($this->mapping['note']) ? (int)$this->mapping['note'] : null;

                $matIdentifier = ($matIdentifierIdx !== null && isset($row[$matIdentifierIdx])) ? trim($row[$matIdentifierIdx]) : null;
                $typeStr       = ($typeIdx !== null && isset($row[$typeIdx])) ? strtolower(trim($row[$typeIdx])) : null;
                $quantityStr   = ($quantityIdx !== null && isset($row[$quantityIdx])) ? trim($row[$quantityIdx]) : null;
                $note          = ($noteIdx !== null && isset($row[$noteIdx])) ? trim($row[$noteIdx]) : null;

                if (empty($matIdentifier)) {
                    $errors[] = "Baris {$processed}: Identitas Material (Kode/Nama) kosong.";
                    continue;
                }

                // 1. CARI MATERIAL BERDASARKAN KODE ATAU NAMA
                $material = Material::where('code', $matIdentifier)
                                    ->orWhere('name', 'like', '%' . $matIdentifier . '%')
                                    ->first();

                if (!$material) {
                    $errors[] = "Baris {$processed}: Barang dengan kode/nama '{$matIdentifier}' tidak terdaftar.";
                    continue;
                }

                // 2. VALIDASI & MAPPING TIPE ARUS (ENUM MUTASI)
                $finalType = null;
                if (in_array($typeStr, ['in', 'masuk', '1', 'barang masuk'])) {
                    $finalType = 'in';
                } elseif (in_array($typeStr, ['out', 'keluar', '0', 'barang keluar'])) {
                    $finalType = 'out';
                }

                if (!$finalType) {
                    $errors[] = "Baris {$processed}: Tipe arus '{$typeStr}' tidak valid (Gunakan: masuk/keluar).";
                    continue;
                }

                // 3. VALIDASI QUANTITY
                $quantity = (int)$quantityStr;
                if ($quantity <= 0) {
                    $errors[] = "Baris {$processed}: Kuantitas volume harus berupa angka bulat positif di atas 0.";
                    continue;
                }

                // Rekam transaksi mutasi baru
                StockMutation::create([
                    'id'          => (string) Str::uuid(),
                    'material_id' => $material->id,
                    'type'        => $finalType,
                    'quantity'    => $quantity,
                    'note'        => $note ?: 'Impor Sistem Asinkronus'
                ]);

            } catch (\Exception $e) {
                $errors[] = "Baris {$processed}: Gagal karena eror sistem (" . $e->getMessage() . ")";
            }

            // Update cache real-time progress bar
            Cache::put("import_progress_mut_{$this->userId}", [
                'status'     => 'processing',
                'current'    => $processed,
                'total'      => $totalRows,
                'percentage' => round(($processed / $totalRows) * 100),
                'errors'     => $errors
            ], 600);
        }

        fclose($file);
        Storage::disk('local')->delete($this->filePath);

        Cache::put("import_progress_mut_{$this->userId}", [
            'status'     => 'completed',
            'current'    => $processed,
            'total'      => $totalRows,
            'percentage' => 100,
            'errors'     => $errors
        ], 600);
    }
}