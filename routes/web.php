<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnlinePortalController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\LeadershipController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\SecurityController as AdminSecurityController;

// Online Portal Routes
Route::get('/services', [OnlinePortalController::class, 'serviceCatalog'])->name('onlineportal.service.catalog');
Route::get('/services/{slug}', [OnlinePortalController::class, 'serviceDetail'])->name('onlineportal.service.detail');
Route::get('/services/{slug}/apply', [OnlinePortalController::class, 'applicationForm'])->name('onlineportal.service.apply');
Route::post('/services/{slug}/apply', [OnlinePortalController::class, 'submitApplication'])->middleware('throttle:public-forms')->name('onlineportal.service.submit');
Route::get('/application/success/{ticketNumber}', [OnlinePortalController::class, 'applicationSuccess'])->name('onlineportal.application.success');
Route::get('/tracking', [OnlinePortalController::class, 'trackTicketForm'])->name('onlineportal.track.ticket.form');
Route::post('/tracking', [OnlinePortalController::class, 'trackTicket'])->middleware('throttle:30,1')->name('onlineportal.track.ticket.result');

// Online Portal Authenticated Routes (Requires Email Verification for pemohon role only)
Route::middleware(['auth', 'check.email.verification'])->prefix('portal')->name('onlineportal.')->group(function () {
    Route::get('/dashboard', [OnlinePortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-tickets', [OnlinePortalController::class, 'myTickets'])->name('my-tickets');
    Route::get('/tickets/{ticketNumber}', [OnlinePortalController::class, 'ticketDetail'])->name('ticket.detail');
    Route::get('/tickets/{ticketNumber}/download', [OnlinePortalController::class, 'downloadOutput'])->name('ticket.download');
});

// Supervision Routes
Route::get('/complaints', [SupervisionController::class, 'complaints'])->name('supervision.complaints.dashboard');
Route::get('/complaints/submit', [SupervisionController::class, 'submitComplaintForm'])->name('supervision.complaint.submit');
Route::post('/complaints/submit', [SupervisionController::class, 'submitComplaint'])->middleware('throttle:public-forms')->name('supervision.complaint.submit.store');
Route::get('/complaints/success/{complaintNumber}', [SupervisionController::class, 'complaintSuccess'])->name('supervision.complaint.success');
Route::get('/complaints/track', [SupervisionController::class, 'showTrackForm'])->name('supervision.complaint.track.form');
Route::post('/complaints/track', [SupervisionController::class, 'trackComplaint'])->middleware('throttle:30,1')->name('supervision.complaint.track');

Route::get('/whistleblowing', [SupervisionController::class, 'whistleblowingForm'])->name('supervision.whistleblowing.form');
Route::post('/whistleblowing', [SupervisionController::class, 'submitWhistleblowing'])->middleware('throttle:public-forms')->name('supervision.whistleblowing.submit');
Route::get('/whistleblowing/success/{complaintNumber}', [SupervisionController::class, 'whistleblowingSuccess'])->name('supervision.whistleblowing.success');

// New Survey System Routes (Multi-step: Identity, SKM, SPAK)
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

// API for ticket checking in survey
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

// Old survey URL: redirects to the 3-step survey
Route::get('/skm-survey', [SupervisionController::class, 'skmSurveyForm'])->name('supervision.skm.survey');

// Public Routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');
Route::get('/visitor-book', [PublicController::class, 'visitorBook'])->name('public.visitor.book');
Route::post('/visitor-book/submit-visitor', [PublicController::class, 'submitVisitor'])->middleware('throttle:public-forms')->name('public.visitor.submit');
Route::post('/visitor-book/submit-applicant', [PublicController::class, 'submitApplicant'])->middleware('throttle:public-forms')->name('public.applicant.submit');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:public-forms')->name('public.contact.store');

// Front Desk Authenticated Routes (Requires Email Verification for pemohon role only)
Route::middleware(['auth', 'check.email.verification'])->group(function () {
    Route::prefix('frontdesk')->name('frontdesk.')->group(function () {
        Route::get('/dashboard', [FrontDeskController::class, 'dashboard'])->name('dashboard');
        Route::get('/triage', [FrontDeskController::class, 'triage'])->name('triage');
        Route::post('/triage', [FrontDeskController::class, 'processTriage'])->name('triage.process');
        Route::get('/service-application', [FrontDeskController::class, 'serviceApplication'])->name('service.application');
        Route::post('/service-application', [FrontDeskController::class, 'submitServiceApplication'])->name('service.submit');
        Route::get('/service/success/{ticketNumber}', [FrontDeskController::class, 'serviceSuccess'])->name('service.success');
        Route::get('/visitor-book', [FrontDeskController::class, 'visitorBook'])->name('visitor-book');
        Route::get('/active-visitors', [FrontDeskController::class, 'activeVisitors'])->name('active-visitors');
        Route::post('/visitors/{visitor}/checkout', [FrontDeskController::class, 'checkoutVisitor'])->name('visitors.checkout');
        Route::get('/visitors/{visitor}/print', [FrontDeskController::class, 'printVisitorPass'])->name('visitors.print');
        Route::get('/search-visitors', [FrontDeskController::class, 'searchVisitors'])->name('search-visitors');
        Route::post('/tickets/{ticket}/hand-over', [FrontDeskController::class, 'handOver'])->name('tickets.hand-over');
    });
});

// Back Office Routes (Requires Email Verification for pemohon role only)
Route::middleware(['auth', 'check.email.verification'])->prefix('backoffice')->name('backoffice.')->group(function () {
    Route::get('/dashboard', [BackOfficeController::class, 'dashboard'])->name('dashboard');
    Route::get('/tickets/queue', [BackOfficeController::class, 'ticketsQueue'])->name('tickets.queue');
    Route::get('/tickets/my', [BackOfficeController::class, 'myTickets'])->name('tickets.my');
    Route::get('/tickets/all', [BackOfficeController::class, 'allTickets'])->name('tickets.all');
    Route::get('/tickets/{ticketNumber}', [BackOfficeController::class, 'ticketDetail'])->name('tickets.detail');
    Route::get('/search', [BackOfficeController::class, 'searchTickets'])->name('tickets.search');
    Route::get('/reports', [BackOfficeController::class, 'reports'])->name('reports');

    // Ticket actions
    Route::post('/tickets/{ticket}/assign', [BackOfficeController::class, 'assignTicket'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/status', [BackOfficeController::class, 'updateStatus'])->name('tickets.update-status');
    Route::post('/tickets/{ticket}/note', [BackOfficeController::class, 'addNote'])->name('tickets.add-note');
    Route::post('/tickets/{ticket}/upload', [BackOfficeController::class, 'uploadFile'])->name('tickets.upload-file');
    Route::post('/tickets/{ticket}/upload-output', [BackOfficeController::class, 'uploadOutput'])->name('tickets.upload-output');
    Route::post('/tickets/{ticket}/complete-step', [BackOfficeController::class, 'completeWorkflowStep'])->name('tickets.complete-step');
    Route::get('/tickets/{ticket}/download-file/{file}', [BackOfficeController::class, 'downloadFile'])->name('tickets.download-file');
    Route::get('/tickets/{ticket}/download-output', [BackOfficeController::class, 'downloadOutput'])->name('tickets.download-output');
});

// Supervision Management Routes (staff only; the public complaint/survey
// routes above share SupervisionController, so the permission is enforced here)
Route::middleware(['auth', 'check.email.verification', 'permission:supervision.access'])->prefix('supervision')->name('supervision.')->group(function () {
    Route::get('/management', [SupervisionController::class, 'surveyManagement'])->name('management');
    Route::get('/surveys/{surveyId}/results', [SupervisionController::class, 'surveyResults'])->name('survey.results');
    Route::get('/performance', [SupervisionController::class, 'performance'])->name('performance');
});

// Complaint follow-up (Modul 10) and survey reports: admin, supervisor and
// school leaders. {complaint} is numeric so /complaints/create still reaches
// the admin-only route below.
Route::middleware(['auth', 'role:admin|supervisor|kepala_sekolah|kepala_tu'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('complaints', AdminComplaintController::class)->only(['index', 'show', 'edit', 'update'])->where(['complaint' => '[0-9]+']);
    Route::get('/complaints/{complaint}/evidence/{index}', [AdminComplaintController::class, 'downloadEvidence'])->name('complaints.evidence');
    Route::get('/whistleblowing', [AdminComplaintController::class, 'whistleblowingIndex'])->name('whistleblowing.index');
    Route::get('/whistleblowing/{complaint}', [AdminComplaintController::class, 'whistleblowingShow'])->name('whistleblowing.show');
    Route::put('/whistleblowing/{complaint}/status', [AdminComplaintController::class, 'updateWhistleblowingStatus'])->name('whistleblowing.update-status');

    Route::get('/skm-report', [AdminSurveyController::class, 'skmReport'])->name('skm.report');
    Route::get('/spak-report', [AdminSurveyController::class, 'spakReport'])->name('spak.report');
    Route::get('/performance-report', [AdminSurveyController::class, 'performanceReport'])->name('performance.report');
});

// Leadership area (Kepala Sekolah, Kepala TU): executive dashboard (Modul 13)
// and service approvals (Modul 8)
Route::middleware(['auth', 'check.email.verification', 'role:admin|kepala_sekolah|kepala_tu'])->prefix('pimpinan')->name('leadership.')->group(function () {
    Route::get('/', [LeadershipController::class, 'dashboard'])->name('dashboard');
    Route::get('/persetujuan', [LeadershipController::class, 'approvals'])->name('approvals');
    Route::post('/persetujuan/{ticket}', [LeadershipController::class, 'decide'])->name('approvals.decide');
});

// Ticket documents: private files served only to the applicant and staff
Route::middleware('auth')->prefix('documents')->name('documents.')->group(function () {
    Route::get('/ticket-files/{file}', [\App\Http\Controllers\TicketDocumentController::class, 'file'])->name('ticket-file');
    Route::get('/ticket-outputs/{output}', [\App\Http\Controllers\TicketDocumentController::class, 'output'])->name('ticket-output');
});

// Profile Routes (Requires Email Verification for pemohon role only)
Route::middleware(['auth', 'check.email.verification'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/set-locale/{locale}', function ($locale) {
    $supportedLocales = config('app.supported_locales', ['en', 'id']);
    if (in_array($locale, $supportedLocales)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back()->withInput();
})->name('set-locale');

// Dashboard Route (Requires Email Verification for pemohon role only)
Route::middleware(['auth', 'check.email.verification'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Admin Dashboard Routes (consolidated from suadmin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard'); // Main admin dashboard route
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.alt'); // Keep for compatibility
    Route::get('/api/service-performance-data', [AdminDashboardController::class, 'getServicePerformanceData'])->name('api.service-performance-data');
    Route::get('/api/complaint-performance-data', [AdminDashboardController::class, 'getComplaintPerformanceData'])->name('api.complaint-performance-data');
    Route::get('/api/whistleblowing-performance-data', [AdminDashboardController::class, 'getWhistleblowingPerformanceData'])->name('api.whistleblowing-performance-data');
    Route::get('/api/survey-analytics-data', [AdminDashboardController::class, 'getSurveyAnalyticsData'])->name('api.survey-analytics-data');

    // Master Data Routes
    Route::resource('services', AdminServiceController::class);

    // Ticket Management
    Route::resource('tickets', AdminTicketController::class);
    Route::post('/tickets/{ticket}/upload-requirement', [AdminTicketController::class, 'uploadRequirement'])->name('tickets.upload.requirement');
    Route::post('/tickets/{ticket}/upload-output', [AdminTicketController::class, 'uploadOutput'])->name('tickets.upload.output');
    Route::get('/tickets/{ticket}/outputs/{output}/edit', [AdminTicketController::class, 'editOutput'])->name('tickets.output.edit');
    Route::put('/tickets/{ticket}/outputs/{output}', [AdminTicketController::class, 'updateOutput'])->name('tickets.output.update');
    Route::post('/tickets/{ticket}/send-ticket-info', [AdminTicketController::class, 'sendTicketInfo'])->name('tickets.send-ticket-info');
    Route::post('/tickets/{ticket}/send-survey-info', [AdminTicketController::class, 'sendSurveyInfo'])->name('tickets.send-survey-info');
    Route::post('/tickets/{ticket}/send-survey', [AdminTicketController::class, 'sendSurvey'])->name('tickets.send-survey');
    Route::post('/tickets/{ticket}/approve', [AdminTicketController::class, 'approveTicket'])->name('tickets.approve');
    Route::post('/tickets/{ticket}/reject', [AdminTicketController::class, 'rejectTicket'])->name('tickets.reject');
    Route::post('/tickets/{ticket}/upload-result', [AdminTicketController::class, 'uploadResult'])->name('tickets.upload-result');
    Route::post('/tickets/{ticket}/mark-ready-pickup', [AdminTicketController::class, 'markReadyForPickup'])->name('tickets.mark-ready-pickup');

    // Complaint Management (create/delete: admin only; follow-up routes below)
    Route::resource('complaints', AdminComplaintController::class)->only(['create', 'store', 'destroy']);

    // Survey Management
    Route::get('/survey-management', [AdminSurveyController::class, 'management'])->name('survey.management');
    Route::get('/survey/archive/{id}', [AdminSurveyController::class, 'getArchiveDetail'])->name('survey.archive.detail');

    // Survey API Endpoints
    Route::prefix('survey/api')->name('survey.api.')->group(function () {
        // Question Management
        Route::get('/identity-questions', [AdminSurveyController::class, 'getIdentityQuestions'])->name('identity-questions');
        Route::get('/skm-questions', [AdminSurveyController::class, 'getSkmQuestions'])->name('skm-questions');
        Route::get('/spak-questions', [AdminSurveyController::class, 'getSpakQuestions'])->name('spak-questions');
        Route::get('/questions/{id}', [AdminSurveyController::class, 'getQuestion'])->name('questions.show');
        Route::post('/questions', [AdminSurveyController::class, 'createQuestion'])->name('questions.create');
        Route::put('/questions/{id}', [AdminSurveyController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{id}', [AdminSurveyController::class, 'deleteQuestion'])->name('questions.delete');

        // Edition Management
        Route::get('/editions', [AdminSurveyController::class, 'getEditions'])->name('editions');
        Route::post('/editions', [AdminSurveyController::class, 'createEdition'])->name('editions.create');
        Route::put('/editions/{id}', [AdminSurveyController::class, 'updateEdition'])->name('editions.update');
        Route::delete('/editions/{id}', [AdminSurveyController::class, 'deleteEdition'])->name('editions.delete');

        // Unsur Management
        Route::get('/unsurs', [AdminSurveyController::class, 'getUnsurs'])->name('unsurs');
        Route::post('/unsurs', [AdminSurveyController::class, 'createUnsur'])->name('unsurs.create');
        Route::put('/unsurs/{id}', [AdminSurveyController::class, 'updateUnsur'])->name('unsurs.update');
        Route::delete('/unsurs/{id}', [AdminSurveyController::class, 'deleteUnsur'])->name('unsurs.delete');
    });

    // Security Routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/dashboard', [AdminSecurityController::class, 'dashboard'])->name('dashboard');
        Route::post('/scan/run', [AdminSecurityController::class, 'runSecurityScan'])->name('scan.run');
        Route::get('/scan/results', [AdminSecurityController::class, 'getScanResults'])->name('scan-results');
        Route::get('/logs', [AdminSecurityController::class, 'logs'])->name('logs');
        Route::get('/blocked-ips', [AdminSecurityController::class, 'blockedIPs'])->name('blocked-ips');
        Route::post('/blocked-ips/block', [AdminSecurityController::class, 'blockIP'])->name('blocked-ips.block');
        Route::post('/blocked-ips/unblock', [AdminSecurityController::class, 'unblockIP'])->name('blocked-ips.unblock');
        Route::get('/rate-limiting', [AdminSecurityController::class, 'rateLimitConfig'])->name('rate-limiting');
        Route::post('/rate-limiting/update', [AdminSecurityController::class, 'updateRateLimitConfig'])->name('rate-limiting.update');
        Route::get('/maintenance', [AdminSecurityController::class, 'maintenanceMode'])->name('maintenance');
        Route::post('/maintenance/enable', [AdminSecurityController::class, 'enableMaintenanceMode'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [AdminSecurityController::class, 'disableMaintenanceMode'])->name('maintenance.disable');
        Route::post('/cache/clear', [AdminSecurityController::class, 'clearCache'])->name('cache.clear');
        Route::get('/api/maintenance-status', [AdminSecurityController::class, 'getMaintenanceStatus']);
    });
});




// Authentication Routes (these will be handled by Laravel Breeze)
require __DIR__.'/auth.php';