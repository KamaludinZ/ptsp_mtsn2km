<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnlinePortalController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ContactController;

// Online Portal Routes
Route::get('/services', [OnlinePortalController::class, 'serviceCatalog'])->name('onlineportal.service.catalog');
Route::get('/services/{slug}', [OnlinePortalController::class, 'serviceDetail'])->name('onlineportal.service.detail');
Route::get('/services/{slug}/apply', [OnlinePortalController::class, 'applicationForm'])->name('onlineportal.service.apply');
Route::post('/services/{slug}/apply', [OnlinePortalController::class, 'submitApplication'])->name('onlineportal.service.submit');
Route::get('/application/success/{ticketNumber}', [OnlinePortalController::class, 'applicationSuccess'])->name('onlineportal.application.success');
Route::get('/tracking', [OnlinePortalController::class, 'trackTicketForm'])->name('onlineportal.track.ticket.form');
Route::post('/tracking', [OnlinePortalController::class, 'trackTicket'])->name('onlineportal.track.ticket.result');

// Online Portal Authenticated Routes (Requires Email Verification)
Route::middleware(['auth', 'verified'])->prefix('portal')->name('onlineportal.')->group(function () {
    Route::get('/dashboard', [OnlinePortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-tickets', [OnlinePortalController::class, 'myTickets'])->name('my-tickets');
    Route::get('/tickets/{ticketNumber}', [OnlinePortalController::class, 'ticketDetail'])->name('ticket.detail');
    Route::get('/tickets/{ticketNumber}/download', [OnlinePortalController::class, 'downloadOutput'])->name('ticket.download');
});

// Supervision Routes
Route::get('/complaints', [SupervisionController::class, 'complaints'])->name('supervision.complaints.dashboard');
Route::get('/complaints/submit', [SupervisionController::class, 'submitComplaintForm'])->name('supervision.complaint.submit');
Route::post('/complaints/submit', [SupervisionController::class, 'submitComplaint'])->name('supervision.complaint.submit.store');
Route::get('/complaints/success/{complaintNumber}', [SupervisionController::class, 'complaintSuccess'])->name('supervision.complaint.success');
Route::get('/complaints/track', [SupervisionController::class, 'showTrackForm'])->name('supervision.complaint.track.form');
Route::post('/complaints/track', [SupervisionController::class, 'trackComplaint'])->name('supervision.complaint.track');

Route::get('/whistleblowing', [SupervisionController::class, 'whistleblowingForm'])->name('supervision.whistleblowing.form');
Route::post('/whistleblowing', [SupervisionController::class, 'submitWhistleblowing'])->name('supervision.whistleblowing.submit');
Route::get('/whistleblowing/success/{complaintNumber}', [SupervisionController::class, 'whistleblowingSuccess'])->name('supervision.whistleblowing.success');

// New Survey System Routes (Multi-step: Identity, SKM, SPAK)
Route::prefix('survey')->name('survey.')->group(function () {
    Route::get('/', [SurveyController::class, 'showForm'])->name('form');
    Route::post('/step1', [SurveyController::class, 'storeStep1'])->name('step1.store');
    Route::get('/step2', [SurveyController::class, 'showStep2'])->name('step2');
    Route::post('/step2', [SurveyController::class, 'storeStep2'])->name('step2.store');
    Route::get('/step3', [SurveyController::class, 'showStep3'])->name('step3');
    Route::post('/step3', [SurveyController::class, 'storeStep3'])->name('step3.store');
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
                    'name' => $ticket->user->name,
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
})->name('api.check.ticket');

// Old survey routes (deprecated, keeping for backward compatibility)
Route::get('/skm-survey', [SupervisionController::class, 'skmSurveyForm'])->name('supervision.skm.survey');
Route::post('/skm-survey', [SupervisionController::class, 'submitSkmSurvey'])->name('supervision.skm.submit');
Route::post('/skm-survey/validate-ticket', [SupervisionController::class, 'validateTicketCode'])->name('supervision.skm.validate-ticket');
Route::get('/survey/success-old', [SupervisionController::class, 'surveySuccess'])->name('supervision.survey.success');

// Public Routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');
Route::get('/visitor-book', [PublicController::class, 'visitorBook'])->name('public.visitor.book');
Route::post('/visitor-book/submit-visitor', [PublicController::class, 'submitVisitor'])->name('public.visitor.submit');
Route::post('/visitor-book/submit-applicant', [PublicController::class, 'submitApplicant'])->name('public.applicant.submit');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::post('/contact', [ContactController::class, 'store'])->name('public.contact.store');

// Front Desk Authenticated Routes (Requires Email Verification)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('frontdesk')->name('frontdesk.')->group(function () {
        Route::get('/dashboard', [FrontDeskController::class, 'dashboard'])->name('dashboard');
        Route::get('/triage', [FrontDeskController::class, 'triage'])->name('triage');
        Route::post('/triage', [FrontDeskController::class, 'processTriage'])->name('triage.process');
        Route::get('/service-application', [FrontDeskController::class, 'serviceApplication'])->name('service.application');
        Route::post('/service-application', [FrontDeskController::class, 'submitServiceApplication'])->name('service.submit');
        Route::get('/service/success/{ticketNumber}', [FrontDeskController::class, 'serviceSuccess'])->name('service.success');
        Route::get('/active-visitors', [FrontDeskController::class, 'activeVisitors'])->name('active-visitors');
        Route::post('/visitors/{visitor}/checkout', [FrontDeskController::class, 'checkoutVisitor'])->name('visitors.checkout');
        Route::get('/visitors/{visitor}/print', [FrontDeskController::class, 'printVisitorPass'])->name('visitors.print');
        Route::get('/search-visitors', [FrontDeskController::class, 'searchVisitors'])->name('search-visitors');
    });
});

// Back Office Routes (Requires Email Verification)
Route::middleware(['auth', 'verified'])->prefix('backoffice')->name('backoffice.')->group(function () {
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

// Supervision Management Routes (Requires Email Verification)
Route::middleware(['auth', 'verified'])->prefix('supervision')->name('supervision.')->group(function () {
    Route::get('/management', [SupervisionController::class, 'surveyManagement'])->name('management');
    Route::get('/surveys/{surveyId}/results', [SupervisionController::class, 'surveyResults'])->name('survey.results');
    Route::get('/performance', [SupervisionController::class, 'performance'])->name('performance');
});

// Profile Routes (Requires Email Verification)
Route::middleware(['auth', 'verified'])->group(function () {
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

// Dashboard Route (Requires Email Verification)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

// Super Admin Root Redirect
Route::redirect('/suadmin', '/suadmin/dashboard');

// Super Admin Dashboard Routes (Replaces Filament)
Route::middleware(['auth', 'verified'])->prefix('suadmin')->name('suadmin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/service-performance-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getServicePerformanceData'])->name('api.service-performance-data');
    Route::get('/api/complaint-performance-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getComplaintPerformanceData'])->name('api.complaint-performance-data');
    Route::get('/api/whistleblowing-performance-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getWhistleblowingPerformanceData'])->name('api.whistleblowing-performance-data');
    Route::get('/api/survey-analytics-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getSurveyAnalyticsData'])->name('api.survey-analytics-data');
    
    // Master Data Routes
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('service-categories', \App\Http\Controllers\Admin\ServiceCategoryController::class);
    
    // Ticket Management
    Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);
    Route::post('/tickets/{ticket}/upload-requirement', [\App\Http\Controllers\Admin\TicketController::class, 'uploadRequirement'])->name('tickets.upload.requirement');
    Route::post('/tickets/{ticket}/upload-output', [\App\Http\Controllers\Admin\TicketController::class, 'uploadOutput'])->name('tickets.upload.output');
    Route::get('/tickets/{ticket}/outputs/{output}/edit', [\App\Http\Controllers\Admin\TicketController::class, 'editOutput'])->name('tickets.output.edit');
    Route::put('/tickets/{ticket}/outputs/{output}', [\App\Http\Controllers\Admin\TicketController::class, 'updateOutput'])->name('tickets.output.update');
    Route::post('/tickets/{ticket}/send-ticket-info', [\App\Http\Controllers\Admin\TicketController::class, 'sendTicketInfo'])->name('tickets.send-ticket-info');
    Route::post('/tickets/{ticket}/send-survey-info', [\App\Http\Controllers\Admin\TicketController::class, 'sendSurveyInfo'])->name('tickets.send-survey-info');
    Route::post('/tickets/{ticket}/send-survey', [\App\Http\Controllers\Admin\TicketController::class, 'sendSurvey'])->name('tickets.send-survey');
    Route::post('/tickets/{ticket}/approve', [\App\Http\Controllers\Admin\TicketController::class, 'approveTicket'])->name('tickets.approve');
    Route::post('/tickets/{ticket}/reject', [\App\Http\Controllers\Admin\TicketController::class, 'rejectTicket'])->name('tickets.reject');
    Route::post('/tickets/{ticket}/upload-result', [\App\Http\Controllers\Admin\TicketController::class, 'uploadResult'])->name('tickets.upload-result');
    Route::post('/tickets/{ticket}/mark-ready-pickup', [\App\Http\Controllers\Admin\TicketController::class, 'markReadyForPickup'])->name('tickets.mark-ready-pickup');
    
    // Visitor Management
    Route::resource('visitors', \App\Http\Controllers\Admin\VisitorController::class);
    Route::post('/visitors/{visitor}/checkout', [\App\Http\Controllers\Admin\VisitorController::class, 'checkOut'])->name('visitors.checkout');
    
    // Complaint Management
    Route::resource('complaints', \App\Http\Controllers\Admin\ComplaintController::class);
    
    // Whistleblowing Management (specific for admin/super admin)
    Route::get('/whistleblowing', [\App\Http\Controllers\Admin\ComplaintController::class, 'whistleblowingIndex'])->name('whistleblowing.index');
    Route::get('/whistleblowing/{complaint}', [\App\Http\Controllers\Admin\ComplaintController::class, 'whistleblowingShow'])->name('whistleblowing.show');
    Route::put('/whistleblowing/{complaint}/status', [\App\Http\Controllers\Admin\ComplaintController::class, 'updateWhistleblowingStatus'])->name('whistleblowing.update-status');
    
    // Survey Management
    Route::get('/survey-management', [\App\Http\Controllers\Admin\SurveyController::class, 'management'])->name('survey.management');
    Route::get('/skm-report', [\App\Http\Controllers\Admin\SurveyController::class, 'skmReport'])->name('skm.report');
    Route::get('/spak-report', [\App\Http\Controllers\Admin\SurveyController::class, 'spakReport'])->name('spak.report');
    Route::get('/performance-report', [\App\Http\Controllers\Admin\SurveyController::class, 'performanceReport'])->name('performance.report');
    Route::get('/survey/archive/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'getArchiveDetail'])->name('survey.archive.detail');

    // Survey API Endpoints
    Route::prefix('survey/api')->name('survey.api.')->group(function () {
        // Question Management
        Route::get('/identity-questions', [\App\Http\Controllers\Admin\SurveyController::class, 'getIdentityQuestions'])->name('identity-questions');
        Route::get('/skm-questions', [\App\Http\Controllers\Admin\SurveyController::class, 'getSkmQuestions'])->name('skm-questions');
        Route::get('/spak-questions', [\App\Http\Controllers\Admin\SurveyController::class, 'getSpakQuestions'])->name('spak-questions');
        Route::get('/questions/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'getQuestion'])->name('questions.show');
        Route::post('/questions', [\App\Http\Controllers\Admin\SurveyController::class, 'createQuestion'])->name('questions.create');
        Route::put('/questions/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'deleteQuestion'])->name('questions.delete');

        // Edition Management
        Route::get('/editions', [\App\Http\Controllers\Admin\SurveyController::class, 'getEditions'])->name('editions');
        Route::post('/editions', [\App\Http\Controllers\Admin\SurveyController::class, 'createEdition'])->name('editions.create');
        Route::put('/editions/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'updateEdition'])->name('editions.update');
        Route::delete('/editions/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'deleteEdition'])->name('editions.delete');

        // Unsur Management
        Route::get('/unsurs', [\App\Http\Controllers\Admin\SurveyController::class, 'getUnsurs'])->name('unsurs');
        Route::post('/unsurs', [\App\Http\Controllers\Admin\SurveyController::class, 'createUnsur'])->name('unsurs.create');
        Route::put('/unsurs/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'updateUnsur'])->name('unsurs.update');
        Route::delete('/unsurs/{id}', [\App\Http\Controllers\Admin\SurveyController::class, 'deleteUnsur'])->name('unsurs.delete');
    });

    // Security Routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\SecurityController::class, 'dashboard'])->name('dashboard');
        Route::post('/scan/run', [\App\Http\Controllers\Admin\SecurityController::class, 'runSecurityScan'])->name('scan.run');
        Route::get('/scan/results', [\App\Http\Controllers\Admin\SecurityController::class, 'getScanResults'])->name('scan-results');
        Route::get('/logs', [\App\Http\Controllers\Admin\SecurityController::class, 'logs'])->name('logs');
        Route::get('/blocked-ips', [\App\Http\Controllers\Admin\SecurityController::class, 'blockedIPs'])->name('blocked-ips');
        Route::post('/blocked-ips/block', [\App\Http\Controllers\Admin\SecurityController::class, 'blockIP'])->name('blocked-ips.block');
        Route::post('/blocked-ips/unblock', [\App\Http\Controllers\Admin\SecurityController::class, 'unblockIP'])->name('blocked-ips.unblock');
        Route::get('/rate-limiting', [\App\Http\Controllers\Admin\SecurityController::class, 'rateLimitConfig'])->name('rate-limiting');
        Route::post('/rate-limiting/update', [\App\Http\Controllers\Admin\SecurityController::class, 'updateRateLimitConfig'])->name('rate-limiting.update');
        Route::get('/maintenance', [\App\Http\Controllers\Admin\SecurityController::class, 'maintenanceMode'])->name('maintenance');
        Route::post('/maintenance/enable', [\App\Http\Controllers\Admin\SecurityController::class, 'enableMaintenanceMode'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [\App\Http\Controllers\Admin\SecurityController::class, 'disableMaintenanceMode'])->name('maintenance.disable');
        Route::post('/cache/clear', [\App\Http\Controllers\Admin\SecurityController::class, 'clearCache'])->name('cache.clear');
    });
    
    // Announcement Management Routes
    Route::get('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'adminIndex'])->name('pengumuman.index');
    Route::get('/pengumuman/create', [\App\Http\Controllers\PengumumanController::class, 'create'])->name('pengumuman.create');
    Route::post('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::get('/pengumuman/{pengumuman}/edit', [\App\Http\Controllers\PengumumanController::class, 'edit'])->name('pengumuman.edit');
    Route::put('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [\App\Http\Controllers\Admin\SettingsController::class, 'reset'])->name('settings.reset');

    // Role Management
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::post('/roles/{role}/clone', [\App\Http\Controllers\Admin\RoleController::class, 'clone'])->name('roles.clone');
    Route::post('/roles/{role}/assign-permissions', [\App\Http\Controllers\Admin\RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
    Route::get('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'getPermissions'])->name('roles.permissions');

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserManagementController::class);
    Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/bulk-action', [\App\Http\Controllers\Admin\UserManagementController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('/users/export', [\App\Http\Controllers\Admin\UserManagementController::class, 'export'])->name('users.export');
});

// Admin Management Routes (Requires Email Verification)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('service-categories', \App\Http\Controllers\Admin\ServiceCategoryController::class);

    // Announcement Management Routes
    Route::get('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'adminIndex'])->name('pengumuman.index');
    Route::get('/pengumuman/create', [\App\Http\Controllers\PengumumanController::class, 'create'])->name('pengumuman.create');
    Route::post('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::get('/pengumuman/{pengumuman}/edit', [\App\Http\Controllers\PengumumanController::class, 'edit'])->name('pengumuman.edit');
    Route::put('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

    // Security Management Routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\SecurityController::class, 'dashboard'])->name('dashboard');
        Route::post('/scan/run', [\App\Http\Controllers\Admin\SecurityController::class, 'runSecurityScan'])->name('scan.run');
        Route::get('/scan/results', [\App\Http\Controllers\Admin\SecurityController::class, 'getScanResults'])->name('scan-results');
        Route::get('/logs', [\App\Http\Controllers\Admin\SecurityController::class, 'logs'])->name('logs');
        Route::get('/blocked-ips', [\App\Http\Controllers\Admin\SecurityController::class, 'blockedIPs'])->name('blocked-ips');
        Route::post('/blocked-ips/block', [\App\Http\Controllers\Admin\SecurityController::class, 'blockIP'])->name('blocked-ips.block');
        Route::post('/blocked-ips/unblock', [\App\Http\Controllers\Admin\SecurityController::class, 'unblockIP'])->name('blocked-ips.unblock');
        Route::get('/rate-limiting', [\App\Http\Controllers\Admin\SecurityController::class, 'rateLimitConfig'])->name('rate-limiting');
        Route::post('/rate-limiting/update', [\App\Http\Controllers\Admin\SecurityController::class, 'updateRateLimitConfig'])->name('rate-limiting.update');
        Route::get('/maintenance', [\App\Http\Controllers\Admin\SecurityController::class, 'maintenanceMode'])->name('maintenance');
        Route::post('/maintenance/enable', [\App\Http\Controllers\Admin\SecurityController::class, 'enableMaintenanceMode'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [\App\Http\Controllers\Admin\SecurityController::class, 'disableMaintenanceMode'])->name('maintenance.disable');
        Route::post('/cache/clear', [\App\Http\Controllers\Admin\SecurityController::class, 'clearCache'])->name('cache.clear');
    });
});



// Authentication Routes (these will be handled by Laravel Breeze)
require __DIR__.'/auth.php';

Route::get('/debug-session', function () {
    return session()->all();
});