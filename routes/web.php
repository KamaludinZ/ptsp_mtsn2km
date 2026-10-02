<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnlinePortalController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

/*
| Public site. Staff work in the control panel (/cp) and applicants in the
| portal (/portal); both are Filament panels (App\Providers\Filament).
*/

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');
Route::get('/faq', [PublicController::class, 'faq'])->name('public.faq');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:public-forms')->name('public.contact.store');
Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');
Route::get('/visitor-book', [PublicController::class, 'visitorBook'])->name('public.visitor.book');
Route::post('/visitor-book/submit-visitor', [PublicController::class, 'submitVisitor'])->middleware('throttle:public-forms')->name('public.visitor.submit');
Route::post('/visitor-book/submit-applicant', [PublicController::class, 'submitApplicant'])->middleware('throttle:public-forms')->name('public.applicant.submit');

// Service catalogue and ticket tracking. Applying happens in the portal.
Route::get('/services', [OnlinePortalController::class, 'serviceCatalog'])->name('onlineportal.service.catalog');
Route::get('/services/{slug}', [OnlinePortalController::class, 'serviceDetail'])->name('onlineportal.service.detail');
Route::get('/services/{slug}/template/{template}', [OnlinePortalController::class, 'downloadTemplate'])->name('onlineportal.service.template');
Route::get('/services/{slug}/apply', fn (string $slug) => redirect('/portal/ajukan?layanan=' . urlencode($slug)))->name('onlineportal.service.apply');
Route::get('/tracking', [OnlinePortalController::class, 'trackTicketForm'])->name('onlineportal.track.ticket.form');
Route::post('/tracking', [OnlinePortalController::class, 'trackTicket'])->middleware('throttle:30,1')->name('onlineportal.track.ticket.result');

// Complaints (Dumas) and whistleblowing from the public
Route::get('/complaints', [SupervisionController::class, 'complaints'])->name('supervision.complaints.dashboard');
// The complaint and whistleblowing forms both live on /complaints; the old form URLs just point there.
Route::redirect('/complaints/submit', '/complaints', 301);
Route::post('/complaints/submit', [SupervisionController::class, 'submitComplaint'])->middleware('throttle:public-forms')->name('supervision.complaint.submit.store');
Route::post('/complaints/saran', [SupervisionController::class, 'submitSuggestion'])->middleware('throttle:public-forms')->name('supervision.suggestion.submit');
Route::get('/complaints/success/{complaintNumber}', [SupervisionController::class, 'complaintSuccess'])->name('supervision.complaint.success');
Route::get('/complaints/track', [SupervisionController::class, 'showTrackForm'])->name('supervision.complaint.track.form');
Route::post('/complaints/track', [SupervisionController::class, 'trackComplaint'])->middleware('throttle:30,1')->name('supervision.complaint.track');
Route::redirect('/whistleblowing', '/complaints?tab=whistleblowing', 301);
Route::post('/whistleblowing', [SupervisionController::class, 'submitWhistleblowing'])->middleware('throttle:public-forms')->name('supervision.whistleblowing.submit');
Route::get('/whistleblowing/success/{complaintNumber}', [SupervisionController::class, 'whistleblowingSuccess'])->name('supervision.whistleblowing.success');

// Satisfaction survey (SKM/SPAK)
Route::prefix('survey')->name('survey.')->group(function () {
    Route::get('/', [SurveyController::class, 'showForm'])->name('form');
    Route::post('/step1', [SurveyController::class, 'storeStep1'])->middleware('throttle:public-forms')->name('step1.store');
    Route::get('/step2', [SurveyController::class, 'showStep2'])->name('step2');
    Route::post('/step2', [SurveyController::class, 'storeStep2'])->middleware('throttle:public-forms')->name('step2.store');
    Route::get('/step3', [SurveyController::class, 'showStep3'])->name('step3');
    Route::post('/step3', [SurveyController::class, 'storeStep3'])->middleware('throttle:public-forms')->name('step3.store');
    Route::get('/success', [SurveyController::class, 'success'])->name('success');
    Route::get('/results', [SurveyController::class, 'results'])->name('results');
});
Route::redirect('/skm-survey', '/survey')->name('supervision.skm.survey');

Route::get('/api/check-ticket/{ticketNumber}', function($ticketNumber) {
    $ticket = \App\Models\Ticket::with(['user', 'service'])->where('ticket_number', $ticketNumber)->first();
    
    if ($ticket) {
        return response()->json([
            'exists' => true,
            'has_survey_completed' => $ticket->hasSurveyCompleted(),
            'ticket' => [
                'id' => $ticket->id,
                'service' => [
                    'name' => $ticket->service->name,
                ],
                'user' => [
                    // Public endpoint: only a masked name, e.g. "Budi S."
                    'name' => \Illuminate\Support\Str::of(optional($ticket->user)->name ?? '')
                        ->explode(' ')
                        ->filter()
                        ->map(fn ($part, $i) => $i === 0 ? $part : mb_substr($part, 0, 1) . '.')
                        ->implode(' '),
                ],
                'status' => $ticket->status,
            ]
        ]);
    }
    
    return response()->json([
        'exists' => false,
        'has_survey_completed' => false,
        'ticket' => null
    ]);
})->middleware('throttle:30,1')->name('api.check.ticket');

Route::get('/set-locale/{locale}', function ($locale) {
    $supportedLocales = config('app.supported_locales', ['en', 'id']);
    if (in_array($locale, $supportedLocales)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back()->withInput();
})->name('set-locale');

// Signed-in users: /dashboard sends each account to its own panel
Route::middleware(['auth', 'check.email.verification'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', fn () => redirect(auth()->user()->isStaff() ? '/cp/profile' : '/portal/profile'))->name('profile.edit');
});

// Printable guest pass for the counter (opened from the Buku Tamu page in /cp)
Route::middleware('auth')->get('/buku-tamu/{visitor}/cetak', fn (\App\Models\Visitor $visitor) => view('print.visitor-pass', compact('visitor')))
    ->can('view', 'visitor')
    ->name('visitors.print');

// Printable receipt of a service request, for the applicant and staff
Route::middleware('auth')->get('/tiket/{ticket}/tanda-terima', fn (\App\Models\Ticket $ticket) => view('print.ticket-receipt', ['ticket' => $ticket->load(['user', 'service'])]))
    ->can('view', 'ticket')
    ->name('tickets.receipt');

// Printable disposition sheet: printed and signed (TTD) or saved as PDF and signed electronically (TTE)
Route::middleware('auth')->get('/tiket/{ticket}/lembar-disposisi', fn (\App\Models\Ticket $ticket) => view('print.disposition-sheet', ['ticket' => $ticket->load(['user', 'service'])]))
    ->can('approve', 'ticket')
    ->name('tickets.disposition-sheet');

// Evidence attached to complaint/whistleblowing reports: private files for complaint handlers
Route::middleware('auth')->get('/pengaduan/{complaint}/bukti/{index}', function (\App\Models\Complaint $complaint, int $index) {
    $path = \App\Services\ComplaintService::evidence($complaint)[$index] ?? abort(404);

    return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
})->can('view', 'complaint')->whereNumber('index')->name('complaints.evidence');

// Ticket documents: private files served only to the applicant and staff
Route::middleware('auth')->prefix('documents')->name('documents.')->group(function () {
    Route::get('/ticket-files/{file}', [\App\Http\Controllers\TicketDocumentController::class, 'file'])->name('ticket-file');
    Route::get('/ticket-outputs/{output}', [\App\Http\Controllers\TicketDocumentController::class, 'output'])->name('ticket-output');
});

// Addresses of the former role dashboards, kept for bookmarks and e-mails
Route::redirect('/admin', '/cp')->name('admin.dashboard');
Route::redirect('/admin/dashboard', '/cp');
Route::redirect('/pimpinan', '/cp');
Route::redirect('/pimpinan/persetujuan', '/cp/pimpinan/disposisi');
Route::redirect('/cp/pimpinan/persetujuan', '/cp/pimpinan/disposisi');
Route::redirect('/frontdesk/dashboard', '/cp');
Route::redirect('/backoffice/dashboard', '/cp');
Route::redirect('/supervision/management', '/cp');
Route::redirect('/portal/dashboard', '/portal');
Route::redirect('/portal/my-tickets', '/portal/permohonan');
Route::get('/application/success/{ticketNumber}', fn () => redirect('/portal/permohonan'))->name('onlineportal.application.success');

require __DIR__.'/auth.php';
