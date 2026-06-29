<?php

namespace App\Http\Controllers;

use App\Jobs\ExportStockMutationsJob;
use App\Jobs\ImportStockMutationsJob;
use App\Models\StockMutation;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StockMutationController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relasi ke material
        $query = StockMutation::with('material');

        // Filter: Pencarian berdasarkan catatan (note)
        if ($request->filled('search')) {
            $query->where('note', 'like', '%' . $request->search . '%');
        }

        // Filter: Berdasarkan Tipe Mutasi (in / out)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter: Berdasarkan Material spesifik
        if ($request->filled('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortBy, ['quantity', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return Inertia::render('StockMutations/Index', [
            'mutations' => $query->get(),
            'materials' => Material::all(), // Untuk opsi filter dropdown
            'filters' => $request->only(['search', 'type', 'material_id', 'sort_by', 'sort_order'])
        ]);
    }

    public function create()
    {
        return Inertia::render('StockMutations/Create', [
            'materials' => Material::all() // Datasource untuk opsi pilihan material
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500'
        ]);

        StockMutation::create($request->all());

        return redirect('/stock-mutations')->with('message', 'Log mutasi stok berhasil dicatat.');
    }

    public function edit(StockMutation $stockMutation)
    {
        return Inertia::render('StockMutations/Edit', [
            'mutation' => $stockMutation->load('material')
        ]);
    }

    public function update(Request $request, StockMutation $stockMutation)
    {
        // Hanya izinkan mengedit catatan (note) demi keamanan riwayat data finansial/stok
        $request->validate([
            'note' => 'nullable|string|max:500'
        ]);

        $stockMutation->update([
            'note' => $request->note
        ]);

        return redirect('/stock-mutations')->with('message', 'Catatan mutasi berhasil diperbarui.');
    }

    public function destroy(StockMutation $stockMutation)
    {
        $stockMutation->delete(); // Soft Delete
        return redirect('/stock-mutations')->with('message', 'Log mutasi berhasil dihapus (Soft Delete).');
    }

    public function triggerExport(Request $request)
    {
        $userId = Auth::id();
        $fileName = 'exports/mutations_export_' . $userId . '.csv';

        if (Storage::disk('public')->exists($fileName)) {
            Storage::disk('public')->delete($fileName);
        }

        $filters = $request->only(['search', 'type', 'material_id', 'sort_by', 'sort_order']);
        ExportStockMutationsJob::dispatch($filters, $userId);

        return response()->json(['status' => 'success']);
    }

    public function checkExportStatus()
    {
        $userId = Auth::id();
        $fileName = 'exports/mutations_export_' . $userId . '.csv';
        $fileExists = Storage::disk('public')->exists($fileName);

        return response()->json([
            'ready' => $fileExists,
            'download_url' => $fileExists ? asset('storage/' . $fileName) : null
        ]);
    }

    public function importUpload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $path = $request->file('file')->store('temp_imports', 'local');

        $file = fopen(Storage::disk('local')->path($path), 'r');
        $headers = fgetcsv($file);
        fclose($file);

        return response()->json(['file_path' => $path, 'headers' => $headers]);
    }

    public function importProcess(Request $request)
    {
        $request->validate(['file_path' => 'required|string', 'mapping' => 'required|array']);
        $userId = Auth::id();

        Cache::put("import_progress_mut_{$userId}", [
            'status' => 'pending',
            'current' => 0,
            'total' => 0,
            'percentage' => 0,
            'errors' => []
        ], 600);

        ImportStockMutationsJob::dispatch($request->file_path, $request->mapping, $userId);
        return response()->json(['status' => 'success']);
    }

    public function importProgress()
    {
        $userId = Auth::id();
        return response()->json(Cache::get("import_progress_mut_{$userId}") ?? ['status' => 'idle']);
    }
}