<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use App\Models\StockMutation;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil data agregasi kuantitas statistik untuk ringkasan kartu
        $totalMaterials  = Material::count();
        $totalCategories = Category::where('is_active', true)->count();
        
        $totalIncoming   = StockMutation::where('type', 'in')->sum('quantity');
        $totalOutgoing   = StockMutation::where('type', 'out')->sum('quantity');

        // 2. Ambil 5 riwayat pergerakan stok logistik terbaru (Eager loading relasi material)
        $recentMutations = StockMutation::with('material')
            ->latest()
            ->limit(5)
            ->get();

        // 3. Render ke halaman Dashboard Vue dengan membawa payload data statistik
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_materials'  => $totalMaterials,
                'total_categories' => $totalCategories,
                'total_incoming'   => (int) $totalIncoming,
                'total_outgoing'   => (int) $totalOutgoing,
            ],
            'recent_mutations' => $recentMutations
        ]);
    }
}