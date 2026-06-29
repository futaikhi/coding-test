<?php

namespace App\Http\Controllers;

use App\Models\StockMutation;
use App\Models\Material;
use Illuminate\Http\Request;
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
            'filters'   => $request->only(['search', 'type', 'material_id', 'sort_by', 'sort_order'])
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
            'type'        => 'required|in:in,out',
            'quantity'    => 'required|integer|min:1',
            'note'        => 'nullable|string|max:500'
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
}