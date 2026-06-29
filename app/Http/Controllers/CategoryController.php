<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Implementasi Searching
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Categories/Index', [
            'categories' => $query->latest()->get(),
            'filters' => $request->only(['search'])
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
}