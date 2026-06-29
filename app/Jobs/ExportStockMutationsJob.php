<?php

namespace App\Jobs;

use App\Models\StockMutation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportStockMutationsJob implements ShouldQueue
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
        $query = StockMutation::with('material');

        // Filter: Search Note
        if (!empty($this->filters['search'])) {
            $query->where('note', 'like', '%' . $this->filters['search'] . '%');
        }

        // Filter: Type (in/out)
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        // Filter: Material ID
        if (!empty($this->filters['material_id'])) {
            $query->where('material_id', $this->filters['material_id']);
        }

        // Sorting
        $sortBy = $this->filters['sort_by'] ?? 'created_at';
        $sortOrder = $this->filters['sort_order'] ?? 'desc';
        if (in_array($sortBy, ['quantity', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $mutations = $query->get();
        $fileName = 'exports/mutations_export_' . $this->userId . '.csv';
        
        if (!Storage::disk('public')->exists('exports')) {
            Storage::disk('public')->makeDirectory('exports');
        }

        $filePath = Storage::disk('public')->path($fileName);
        $file = fopen($filePath, 'w');

        // Inject UTF-8 BOM
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header CSV
        fputcsv($file, ['Waktu Transaksi', 'Kode Barang', 'Nama Barang', 'Tipe Arus', 'Volume Kuantitas', 'Keterangan/Note']);

        foreach ($mutations as $mut) {
            fputcsv($file, [
                $mut->created_at ? $mut->created_at->format('Y-m-d H:i') : '-',
                $mut->material ? $mut->material->code : 'DELETED',
                $mut->material ? $mut->material->name : 'Material Tidak Ditemukan',
                $mut->type === 'in' ? 'MASUK (IN)' : 'KELUAR (OUT)',
                $mut->quantity . ' pcs',
                $mut->note ?? '-'
            ]);
        }

        fclose($file);
    }
}