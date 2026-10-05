<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    /**
     * Display a listing of the resource for public.
     */
    public function index(Request $request)
    {
        // Shown through the whole last day (dates compared as dates, not times).
        $query = Pengumuman::active()->with('user:id,name');

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('content', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('author', 'ILIKE', "%{$searchTerm}%");
            });
        }

        // Handle category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Handle date range filter - validate dates are in correct format
        if ($request->filled('start_date')) {
            $startDate = $request->start_date;
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                $query->where('publish_date', '>=', $startDate);
            }
        }

        if ($request->filled('end_date')) {
            $endDate = $request->end_date;
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                $query->where('publish_date', '<=', $endDate);
            }
        }

        // Additional validation: ensure end date is not before start date
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Only apply date range if start date is not after end date
            if (strtotime($startDate) <= strtotime($endDate)) {
                $query->where('publish_date', '>=', $startDate)
                      ->where('publish_date', '<=', $endDate);
            }
        }

        $pengumumen = $query->orderBy('publish_date', 'desc')
                    ->paginate(10)
                    ->appends($request->query());

        // Categories that announcements on the site actually use.
        $categories = collect(Pengumuman::categories(onlyPublished: true))
            ->mapWithKeys(fn (string $category) => [$category => \Illuminate\Support\Str::headline($category)])->all();

        return view('pengumuman.index', compact('pengumumen', 'categories'));
    }

    /** One announcement on the public site. */
    public function show(Pengumuman $pengumuman)
    {
        // Live announcements for everyone; administrators may preview the others
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view', $pengumuman), 404);

        // Counted once per IP address; an administrator's preview doesn't count
        if ($pengumuman->status() === 'tayang') {
            $pengumuman->recordView(request()->ip());
        }

        return view('pengumuman.show', compact('pengumuman'));
    }
}
