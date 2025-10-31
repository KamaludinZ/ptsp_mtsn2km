<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Carbon\Carbon;

class PublicController extends Controller
{
    /**
     * Display home page (landing page)
     */
    public function home()
    {
        return view('welcome');
    }

    /**
     * Display visitor book (public access)
     */
    public function visitorBook()
    {
        $date = request('date', Carbon::today()->toDateString());
        $visitors = Visitor::with(['targetUser', 'checkedInBy', 'checkedOutBy'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('public.visitor-book', compact('visitors', 'date'));
    }

    /**
     * Display about page
     */
    public function about()
    {
        return view('public.about');
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        return view('public.contact');
    }
}