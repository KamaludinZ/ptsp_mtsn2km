<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Visitor;
use Illuminate\Http\Request; // Don't forget to import Request
use Carbon\Carbon;
use App\Services\WhatsAppService;

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
        $visitors = Visitor::whereDate('check_in_time', $date)
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        return view('public.visitor-book', compact('visitors', 'date'));
    }

    /**
     * Display about page
     */
    public function about()
    {
        $faqs = Faq::where('is_active', true)->orderBy('created_at', 'desc')->get();
        return view('public.about', compact('faqs'));
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        return view('public.contact');
    }

    /**
     * Handle visitor form submission
     */
    public function submitVisitor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'institution' => 'nullable|string|max:255',
            'institution_category' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'obscure_name' => 'nullable',
        ]);

        Visitor::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'institution' => $validated['institution'] ?? null,
            'institution_category' => $validated['institution_category'] ?? null,
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'is_obscured' => $request->boolean('obscure_name'),
            'check_in_time' => Carbon::now(),
            'status' => 'active',
        ]);

        return redirect()->route('public.visitor.book')->with('success', 'Tamu berhasil didaftarkan!');
    }

    /**
     * Handle service applicant form submission
     */
    public function submitApplicant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'institution' => 'nullable|string|max:255',
            'institution_category' => 'nullable|string|max:255',
            'applicant_type' => 'nullable|string|max:255',
            'target_service' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'obscure_name' => 'nullable',
        ]);

        Visitor::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'institution' => $validated['institution'] ?? null,
            'institution_category' => $validated['institution_category'] ?? null,
            'purpose' => 'Pemohon Layanan: ' . $validated['target_service'], // Combine purpose
            'notes' => $validated['notes'] ?? null,
            'is_obscured' => $request->boolean('obscure_name'),
            'check_in_time' => Carbon::now(),
            'status' => 'active',
        ]);

        // Send WhatsApp notification to applicant
        $whatsappService = new WhatsAppService();
        $message = "Halo {$validated['name']}! Permohonan layanan Anda ({$validated['target_service']}) telah kami terima. Kami akan segera memprosesnya.";
        $whatsappService->sendMessage($validated['phone'], $message);

        return redirect()->route('public.visitor.book')->with('success', 'Pemohon layanan berhasil didaftarkan!');
    }
}