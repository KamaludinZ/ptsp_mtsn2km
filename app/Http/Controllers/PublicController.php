<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Service;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
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
        return view('welcome', ['stats' => $this->publicStats()]);
    }

    /**
     * Real service figures for the homepage (Permen PANRB 15/2014 asks for
     * transparent performance data). Null means "no data yet".
     */
    private function publicStats(): array
    {
        return Cache::remember('public-home-stats', now()->addMinutes(10), function () {
            // IKM = average unsur score (1-4) x 25, per Permenpan RB 14/2017
            $skmAverage = SurveyAnswer::whereNotNull('rating_value')
                ->whereHas('question', fn ($q) => $q->where('type', 'skm'))
                ->avg('rating_value');

            $completed = Ticket::where('status', 'completed')->whereNotNull('actual_completion_date');
            $averageDays = (clone $completed)
                ->selectRaw('AVG(actual_completion_date - created_at::date) AS days')
                ->value('days');

            $totalTickets = Ticket::count();
            $closedTickets = Ticket::whereIn('status', ['completed', 'rejected'])->count();

            return [
                'ikm' => $skmAverage ? round($skmAverage * 25, 1) : null,
                'average_days' => $averageDays !== null ? max(1, (int) ceil($averageDays)) : null,
                'services' => Service::where('is_active', true)->count(),
                'tickets' => $totalTickets,
                'completed_percent' => $totalTickets ? (int) round(Ticket::where('status', 'completed')->count() / $totalTickets * 100) : null,
                'closed' => $closedTickets,
                'respondents' => SurveyResponse::count(),
            ];
        });
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

        // Counts for the whole day, not just the current page
        $activeCount = Visitor::whereDate('check_in_time', $date)->whereNull('check_out_time')->count();
        $finishedCount = Visitor::whereDate('check_in_time', $date)->whereNotNull('check_out_time')->count();

        $services = Service::where('is_active', true)->orderBy('name')->pluck('name');
        $visitPurposes = Visitor::VISIT_PURPOSES;

        return view('public.visitor-book', compact('visitors', 'date', 'activeCount', 'finishedCount', 'services', 'visitPurposes'));
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
            'purpose' => ['required', 'string', \Illuminate\Validation\Rule::in(Visitor::VISIT_PURPOSES)],
            'purpose_other' => 'required_if:purpose,Lainnya|nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'obscure_name' => 'nullable',
        ], [
            'purpose_other.required_if' => 'Tuliskan tujuan kunjungan Anda.',
        ]);

        $purpose = $validated['purpose'] === 'Lainnya' ? $validated['purpose_other'] : $validated['purpose'];

        Visitor::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'institution' => $validated['institution'] ?? null,
            'institution_category' => $validated['institution_category'] ?? null,
            'purpose' => $purpose,
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
            'target_service' => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'Lainnya' && !Service::where('is_active', true)->where('name', $value)->exists()) {
                    $fail('Pilih layanan dari daftar yang tersedia.');
                }
            }],
            'target_service_other' => 'required_if:target_service,Lainnya|nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'obscure_name' => 'nullable',
        ], [
            'target_service_other.required_if' => 'Tuliskan layanan yang Anda tuju.',
        ]);

        if ($validated['target_service'] === 'Lainnya') {
            $validated['target_service'] = $validated['target_service_other'];
        }

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