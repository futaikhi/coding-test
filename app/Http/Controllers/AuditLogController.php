<?php

namespace App\Http\Controllers;

use OwenIt\Auditing\Models\Audit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data audit terbaru lengkap dengan user yang mengeksekusi aksi
        $query = Audit::with('user');

        // Filter pencarian berdasarkan tipe event (created, updated, deleted)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('module')) {
            // Kita petakan string sederhana ke namespace asli model
            $modelMap = [
                'material' => 'App\Models\Material',
                'category' => 'App\Models\Category',
                'mutation' => 'App\Models\StockMutation',
            ];
            
            $targetModel = $modelMap[$request->module] ?? null;
            if ($targetModel) {
                $query->where('auditable_type', $targetModel);
            }
        }

        return Inertia::render('Audits/Index', [
            'logs' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['event'])
        ]);
    }
}
