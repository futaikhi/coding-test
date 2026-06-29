<?php

namespace App\Http\Controllers;

use App\Jobs\ExportCategoriesJob;
use App\Jobs\ImportCategoriesJob;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Implementasi Searching
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $auditLogs = Audit::with('user')
            ->where('auditable_type', Category::class)
            ->latest()
            ->limit(10) // Batasi 10 histori terbaru saja agar halaman ringan
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $query->latest()->get(),
            'filters' => $request->only(['search']),
            'audit_logs' => $auditLogs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'is_active' => 'required|boolean', // Validasi tipe BOOLEAN
            'attributes' => 'nullable|array'     // Validasi tipe JSON/Array
        ]);

        Category::create([
            'name' => $request->input('name'),
            'is_active' => $request->input('is_active'),
            'attributes' => $request->input('attributes'), // Otomatis di-cast menjadi string JSON oleh model
        ]);

        return redirect('/categories')->with('message', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'is_active' => 'required|boolean',
            'attributes' => 'nullable|array'
        ]);

        $category->update([
            'name' => $request->input('name'),
            'is_active' => $request->input('is_active'),
            'attributes' => $request->input('attributes'),
        ]);

        return redirect('/categories')->with('message', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete(); // Soft Delete
        return redirect('/categories')->with('message', 'Kategori berhasil dihapus (Soft Delete).');
    }

    public function searchApi(Request $request)
    {
        $search = $request->input('q'); // Ambil kata kunci dari vue

        $categories = Category::where('is_active', true)
            ->when($search, function ($query, $search) {
                if ($search != 'null' && $search != null) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->limit(10) // Batasi hanya 10 data agar query super cepat
            ->get();

        // Format wajib agar langsung dibaca oleh komponen multiselect
        return response()->json(
            $categories->map(function ($cat) {
                return [
                    'value' => $cat->id,
                    'label' => $cat->name
                ];
            })
        );
    }

    public function triggerExport(Request $request)
    {
        $userId = Auth::id();
        $fileName = 'exports/categories_export_' . $userId . '.csv';

        if (Storage::disk('public')->exists($fileName)) {
            Storage::disk('public')->delete($fileName);
        }

        $filters = $request->only(['search']);
        ExportCategoriesJob::dispatch($filters, $userId);

        return response()->json(['status' => 'success']);
    }

    public function checkExportStatus()
    {
        $userId = Auth::id();
        $fileName = 'exports/categories_export_' . $userId . '.csv';
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

        Cache::put("import_progress_cat_{$userId}", [
            'status' => 'pending',
            'current' => 0,
            'total' => 0,
            'percentage' => 0,
            'errors' => []
        ], 600);

        ImportCategoriesJob::dispatch($request->file_path, $request->mapping, $userId);
        return response()->json(['status' => 'success']);
    }

    public function importProgress()
    {
        $userId = Auth::id();
        return response()->json(Cache::get("import_progress_cat_{$userId}") ?? ['status' => 'idle']);
    }
}