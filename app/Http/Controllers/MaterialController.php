<?php

namespace App\Http\Controllers;

use App\Jobs\ExportMaterialsJob;
use App\Jobs\ImportMaterialsJob;
use App\Models\Material;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        // 1. Logika Searching, Filtering, & Sorting (Query Builder)
        $query = Material::with('category'); // Eager load relasi ke kategori

        // Fitur Search (Berdasarkan Nama atau Kode Material)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Fitur Filter (Berdasarkan Kategori)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Fitur Sorting (Default: berdasarkan yang terbaru dimasukkan)
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Validasi kolom sort agar aman dari SQL Injection
        if (in_array($sortBy, ['name', 'code', 'published_at', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $auditLogs = Audit::with('user')
            ->where('auditable_type', Material::class)
            ->latest()
            ->limit(10) // Batasi 10 histori terbaru saja agar halaman ringan
            ->get();

        return Inertia::render('Materials/Index', [
            'materials' => $query->get(),
            'categories' => Category::where('is_active', true)->get(), // Untuk opsi filter dropdown
            'filters' => $request->only(['search', 'category_id', 'sort_by', 'sort_order']), // Lempar balik ke Vue untuk state input
            'audit_logs' => $auditLogs,
        ]);
    }

    public function create()
    {
        $year = date('Y');

        // Looping untuk menjamin kode benar-benar unik di database
        do {
            $randomDigits = rand(1000, 9999);
            $generatedCode = "MAT-{$year}-{$randomDigits}";
        } while (Material::where('code', $generatedCode)->exists());

        return Inertia::render('Materials/Create', [
            'material_code' => $generatedCode
        ]);
    }

    public function store(Request $request)
    {
        // 2. Validasi Ketat Sesuai Kriteria Soal Ujian
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|unique:materials,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'published_at' => 'required|date',
            // Ketentuan: PDF Only, ukuran antara 100 KB - 500 KB (Laravel membaca rule min/max file dalam KB)
            'document' => 'required|file|mimes:pdf|min:100|max:500',
        ], [
            'document.mimes' => 'Dokumen lampiran harus berupa file format PDF murni.',
            'document.min' => 'Ukuran file terlalu kecil! Minimal ukuran dokumen adalah 100 KB.',
            'document.max' => 'Ukuran file terlalu besar! Maksimal ukuran dokumen adalah 500 KB.',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            // Simpan ke folder 'materials' di dalam storage/app/public
            $documentPath = $request->file('document')->store('materials', 'public');
        }

        Material::create([
            'category_id' => $request->category_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'published_at' => $request->published_at,
            'document_path' => $documentPath,
        ]);

        return redirect('/materials')->with('message', 'Material berhasil ditambahkan ke inventaris.');
    }

    public function edit(Material $material)
    {
        return Inertia::render('Materials/Edit', [
            'material' => $material,
            'categories' => Category::where('is_active', true)->get()
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|unique:materials,code,' . $material->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'published_at' => 'required|date',
            'document' => 'nullable|file|mimes:pdf|min:100|max:500',
        ], [
            // WAJIB DISERTAKAN AGAR KONSISTEN
            'document.mimes' => 'Dokumen lampiran harus berupa file format PDF murni.',
            'document.min' => 'Ukuran file terlalu kecil! Minimal ukuran dokumen adalah 100 KB.',
            'document.max' => 'Ukuran file terlalu besar! Maksimal ukuran dokumen adalah 500 KB.',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'published_at' => $request->published_at,
        ];

        if ($request->hasFile('document')) {
            // Hapus file lama dari storage agar menghemat ruang server
            if ($material->document_path) {
                Storage::disk('public')->delete($material->document_path);
            }
            // Simpan file baru
            $data['document_path'] = $request->file('document')->store('materials', 'public');
        }

        $material->update($data);

        return redirect('/materials')->with('message', 'Data material berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        // Menggunakan Soft Deletes bawaan trait Laravel (data tidak hilang permanen dari DB)
        $material->delete();
        return redirect('/materials')->with('message', 'Material berhasil dipindahkan ke tempat pembuangan (Soft Delete).');
    }

    public function searchApi(Request $request)
    {
        $search = $request->input('q');

        $materials = Material::when($search, function ($query, $search) {
            if ($search != 'null' && $search != null) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            }
        })
            ->limit(10)
            ->get();

        return response()->json(
            $materials->map(function ($mat) {
                return [
                    'value' => $mat->id,
                    'label' => '[' . $mat->code . '] ' . $mat->name
                ];
            })
        );
    }

    // Fungsi untuk memicu antrean ekspor background

    public function triggerExport(Request $request)
    {
        $userId = Auth::id();
        $fileName = 'exports/materials_export_' . $userId . '.csv';

        // Hapus file ekspor lama milik user ini jika ada
        if (Storage::disk('public')->exists($fileName)) {
            Storage::disk('public')->delete($fileName);
        }

        // Ambil semua request input filter saat ini dari screen front-end
        $filters = $request->only(['search', 'category_id', 'sort_by', 'sort_order']);

        // REVISI: Lempar data filter & ID User ke dalam Job Antrean Queue
        ExportMaterialsJob::dispatch($filters, $userId);

        return response()->json(['message' => 'Antrean ekspor berhasil dijalankan.']);
    }

    public function checkExportStatus()
    {
        $userId = Auth::id();
        // Cek keberadaan file spesifik milik user yang sedang me-request
        $fileName = 'exports/materials_export_' . $userId . '.csv';
        $fileExists = Storage::disk('public')->exists($fileName);

        return response()->json([
            'ready' => $fileExists,
            'download_url' => $fileExists ? asset('storage/' . $fileName) : null
        ]);
    }

    // 1. Menerima file unggahan awal dan mengekstrak nama header kolomnya
    public function importUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt' // CSV murni
        ]);

        // Simpan file sementara di folder local private (bukan folder public public)
        $path = $request->file('file')->store('temp_imports', 'local');

        // Ambil baris teratas (Header)
        $file = fopen(Storage::disk('local')->path($path), 'r');
        $headers = fgetcsv($file);
        fclose($file);

        // Kirim balik ke Vue untuk dijadikan pilihan opsi mapping oleh user
        return response()->json([
            'file_path' => $path,
            'headers' => $headers // Contoh return: ["Nama Barang", "Deskripsi Produk", "Tanggal Masuk"]
        ]);
    }

    // 2. Memicu antrean background job untuk memproses data
    public function importProcess(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array'
        ]);

        $userId = Auth::id();

        // Inisialisasi awal penampung status cache progres
        Cache::put("import_progress_{$userId}", [
            'status' => 'pending',
            'current' => 0,
            'total' => 0,
            'percentage' => 0,
            'errors' => []
        ], 600);

        // Jalankan Antrean Queue Async
        ImportMaterialsJob::dispatch($request->file_path, $request->mapping, $userId);

        return response()->json(['status' => 'success']);
    }

    // 3. API Kecil tempat Vue melakukan Polling Progres secara berkala
    public function importProgress()
    {
        $userId = Auth::id();
        $progress = Cache::get("import_progress_{$userId}");

        return response()->json($progress ?? ['status' => 'idle']);
    }
}