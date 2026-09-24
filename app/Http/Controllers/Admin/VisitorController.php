<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Visitor::query();
        
        // Filter by status (active/checked-out)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('check_out_time');
            } elseif ($request->status === 'checked_out') {
                $query->whereNotNull('check_out_time');
            }
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('check_in_time', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('check_in_time', '<=', $request->date_to);
        }
        
        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('email', 'ilike', '%' . $request->search . '%')
                  ->orWhere('phone', 'ilike', '%' . $request->search . '%')
                  ->orWhere('institution', 'ilike', '%' . $request->search . '%');
            });
        }
        
        $visitors = $query->latest()->paginate(15);
        
        return view('admin.visitors.index', compact('visitors'));
    }

    public function create()
    {
        return view('admin.visitors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'person_to_visit' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_obscured' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('visitors', 'public');
            $data['photo'] = $path;
        }

        Visitor::create($data);

        return redirect()->route('admin.visitors.index')->with('success', 'Visitor berhasil ditambahkan.');
    }

    public function show(Visitor $visitor)
    {
        return view('admin.visitors.show', compact('visitor'));
    }

    public function edit(Visitor $visitor)
    {
        return view('admin.visitors.edit', compact('visitor'));
    }

    public function update(Request $request, Visitor $visitor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'person_to_visit' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_obscured' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($visitor->photo) {
                \Storage::disk('public')->delete($visitor->photo);
            }
            
            $path = $request->file('photo')->store('visitors', 'public');
            $data['photo'] = $path;
        }

        $visitor->update($data);

        return redirect()->route('admin.visitors.index')->with('success', 'Visitor berhasil diperbarui.');
    }

    public function destroy(Visitor $visitor)
    {
        // Delete photo if exists
        if ($visitor->photo) {
            \Storage::disk('public')->delete($visitor->photo);
        }
        
        $visitor->delete();

        return redirect()->route('admin.visitors.index')->with('success', 'Visitor berhasil dihapus.');
    }
    
    public function checkOut(Request $request, Visitor $visitor)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        $visitor->update([
            'check_out_time' => now(),
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('admin.visitors.index')->with('success', 'Visitor berhasil checkout.');
    }
}