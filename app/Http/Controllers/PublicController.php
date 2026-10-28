<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Service;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use App\Models\Visitor;
use App\Models\VisitorMaster;
use App\Services\FrontDeskService;
use Illuminate\Http\Request; // Don't forget to import Request
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PublicController extends Controller
{
    /** Indonesian messages for the public guest-book forms. */
    /**
     * Display home page (landing page)
     */
    public function home()
    {
        return view('welcome', [
            'stats' => $this->publicStats(),
            'slides' => \App\Models\HeroSlider::active()->get(),
        ]);
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
    public function visitorBook(Request $request)
    {
        // A public page: a date or kind it does not understand lists today's
        // guests of every kind instead of failing.
        $filters = Validator::make($request->only('date', 'jenis'), [
            'date' => ['nullable', 'date_format:Y-m-d'],
            'jenis' => ['nullable', Rule::in(array_keys(Visitor::GUEST_TYPES))],
        ])->valid();
        $date = $filters['date'] ?? Carbon::today()->toDateString();
        $type = $filters['jenis'] ?? null;

        $visitors = Visitor::whereDate('check_in_time', $date)
            ->when($type, fn ($query) => $query->where('guest_type', $type))
            ->orderBy('check_in_time', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Counts for the whole day and every kind, not just the current page
        $counts = Visitor::whereDate('check_in_time', $date)->toBase()
            ->selectRaw("count(*) as total, count(*) filter (where check_out_time is null) as active, count(*) filter (where guest_type = 'pemohon') as applicants")
            ->first();
        [$totalCount, $activeCount] = [(int) $counts->total, (int) $counts->active];
        $finishedCount = $totalCount - $activeCount;
        $typeCounts = ['kunjungan' => $totalCount - (int) $counts->applicants, 'pemohon' => (int) $counts->applicants];

        $services = Service::where('is_active', true)->orderBy('name')->pluck('name');
        $visitPurposes = VisitorMaster::options('tujuan');

        return view('public.visitor-book', compact('visitors', 'date', 'type', 'totalCount', 'activeCount', 'finishedCount', 'typeCounts', 'services', 'visitPurposes'));
    }

    /**
     * Display about page
     */
    public function about()
    {
        $faqs = Faq::published()->limit(5)->get();
        return view('public.about', compact('faqs'));
    }

    /** Pertanyaan umum (FAQ), searchable. */
    public function faq()
    {
        $faqs = Faq::published()->get();

        return view('public.faq', compact('faqs'));
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
        if (self::isBot($request)) {
            return redirect()->route('public.visitor.book')->with('success', 'Tamu berhasil didaftarkan!');
        }

        $validated = $request->validate(FrontDeskService::selfRegistrationRules(), FrontDeskService::GUEST_MESSAGES, FrontDeskService::GUEST_ATTRIBUTES);

        $visitor = app(FrontDeskService::class)->register('kunjungan', $validated);

        return redirect()->route('public.visitor.book')
            ->with('success', 'Tamu berhasil didaftarkan!')
            ->with('registered', $visitor->confirmationSummary());
    }

    /**
     * Handle service applicant form submission
     */
    public function submitApplicant(Request $request)
    {
        if (self::isBot($request)) {
            return redirect()->route('public.visitor.book')->with('success', 'Pemohon layanan berhasil didaftarkan!');
        }

        $validated = $request->validate(FrontDeskService::serviceApplicantRules(), FrontDeskService::GUEST_MESSAGES, FrontDeskService::GUEST_ATTRIBUTES);

        $visitor = app(FrontDeskService::class)->register('pemohon', $validated);

        // No WhatsApp here: the form needs no sign-in, so anyone could make the
        // school's number message any phone. The guest sees the confirmation on screen.

        return redirect()->route('public.visitor.book')
            ->with('success', 'Pemohon layanan berhasil didaftarkan!')
            ->with('registered', $visitor->confirmationSummary());
    }

    /** Wrong phone numbers allowed per guest before checkout is locked for a while (anyone guessing, from anywhere). */
    public const CHECKOUT_ATTEMPTS = 5;

    public const CHECKOUT_LOCK_MINUTES = 15;

    /**
     * POST /visitor-book/{visitor}/checkout: a guest checks out from the
     * public visitor book, proving it is them with the phone given on
     * arrival (Visitor::phoneMatches). Recorded as the
     * counter's checkout is (FrontDeskService::checkOut), as the guest's own.
     * A wrong number comes back to the modal ("checkout" error bag).
     */
    public function checkOutVisitor(Request $request, Visitor $visitor)
    {
        $back = redirect()->route('public.visitor.book');
        if (self::isBot($request)) {
            return $back->with('success', 'Checkout berhasil. Terima kasih atas kunjungan Anda.');
        }

        if (! $visitor->canSelfCheckOut()) {
            return $back->withErrors(['checkout' => $visitor->check_out_time
                ? 'Tamu ini sudah checkout.'
                : 'Checkout tamu ini dilakukan oleh petugas di loket.']);
        }

        $refuse = fn (string $message) => $back->withErrors(['phone' => $message], 'checkout')->withInput(['visitor' => (string) $visitor->id]);
        $key = 'visitor-checkout:' . $visitor->id;
        if (RateLimiter::tooManyAttempts($key, self::CHECKOUT_ATTEMPTS)) {
            return $refuse('Terlalu banyak percobaan. Coba lagi dalam ' . ceil(RateLimiter::availableIn($key) / 60) . ' menit atau checkout di loket.');
        }

        $phone = Validator::make($request->only('phone'), ['phone' => ['required', 'string', 'max:20']], FrontDeskService::GUEST_MESSAGES, FrontDeskService::GUEST_ATTRIBUTES);
        if ($phone->fails()) {
            return $refuse($phone->errors()->first('phone'));
        }

        if (! $visitor->phoneMatches($request->input('phone'))) {
            RateLimiter::hit($key, self::CHECKOUT_LOCK_MINUTES * 60);

            return $refuse('Nomor telepon tidak cocok dengan data kunjungan. Periksa kembali nomornya, atau checkout di loket.');
        }

        try {
            app(FrontDeskService::class)->checkOut($visitor, null, $request->ip());
        } catch (\App\Exceptions\TicketActionException $e) {
            return $back->withErrors(['checkout' => $e->getMessage()]);
        }
        RateLimiter::clear($key);

        return $back->with('success', 'Checkout berhasil. Terima kasih atas kunjungan Anda, ' . $visitor->publicName() . '.');
    }

    /**
     * Honeypot: the "website" field is hidden from people, so only bots fill
     * it in. They get the usual success page and nothing is stored.
     */
    private static function isBot(Request $request): bool
    {
        return filled($request->input('website'));
    }
}
