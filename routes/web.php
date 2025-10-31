<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnlinePortalController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProfileController;

// Online Portal Routes
Route::get('/services', [OnlinePortalController::class, 'serviceCatalog'])->name('onlineportal.service.catalog');
Route::get('/services/{slug}', [OnlinePortalController::class, 'serviceDetail'])->name('onlineportal.service.detail');
Route::get('/services/{slug}/apply', [OnlinePortalController::class, 'applicationForm'])->name('onlineportal.service.apply');
Route::post('/services/{slug}/apply', [OnlinePortalController::class, 'submitApplication'])->name('onlineportal.service.submit');
Route::get('/application/success/{ticketNumber}', [OnlinePortalController::class, 'applicationSuccess'])->name('onlineportal.application.success');
Route::get('/tracking', [OnlinePortalController::class, 'trackTicketForm'])->name('onlineportal.track.ticket.form');
Route::post('/tracking', [OnlinePortalController::class, 'trackTicket'])->name('onlineportal.track.ticket.result');

// Online Portal Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [OnlinePortalController::class, 'dashboard'])->name('onlineportal.dashboard');
    Route::get('/my-tickets', [OnlinePortalController::class, 'myTickets'])->name('onlineportal.my-tickets');
    Route::get('/tickets/{ticketNumber}', [OnlinePortalController::class, 'ticketDetail'])->name('onlineportal.ticket.detail');
    Route::get('/tickets/{ticketNumber}/download', [OnlinePortalController::class, 'downloadOutput'])->name('onlineportal.ticket.download');
});

// Supervision Routes
Route::get('/complaints', [SupervisionController::class, 'complaints'])->name('supervision.complaints.dashboard');
Route::get('/complaints/submit', [SupervisionController::class, 'submitComplaintForm'])->name('supervision.complaint.submit');
Route::post('/complaints/submit', [SupervisionController::class, 'submitComplaint'])->name('supervision.complaint.submit.store');
Route::get('/complaints/success/{complaintNumber}', [SupervisionController::class, 'complaintSuccess'])->name('supervision.complaint.success');
Route::post('/complaints/track', [SupervisionController::class, 'trackComplaint'])->name('supervision.complaint.track');

Route::get('/whistleblowing', [SupervisionController::class, 'whistleblowingForm'])->name('supervision.whistleblowing.form');
Route::post('/whistleblowing', [SupervisionController::class, 'submitWhistleblowing'])->name('supervision.whistleblowing.submit');
Route::get('/whistleblowing/success/{complaintNumber}', [SupervisionController::class, 'whistleblowingSuccess'])->name('supervision.whistleblowing.success');

Route::get('/skm-survey', [SupervisionController::class, 'skmSurveyForm'])->name('supervision.skm.survey');
Route::post('/skm-survey', [SupervisionController::class, 'submitSkmSurvey'])->name('supervision.skm.submit');
Route::get('/survey/success', [SupervisionController::class, 'surveySuccess'])->name('supervision.survey.success');

// Public Routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/visitor-book', [PublicController::class, 'visitorBook'])->name('public.visitor.book');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');

// Front Desk Authenticated Routes
Route::middleware(['auth'])->group(function () {
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

// Back Office Routes
Route::middleware(['auth'])->prefix('backoffice')->name('backoffice.')->group(function () {
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

// Supervision Management Routes
Route::middleware(['auth'])->prefix('supervision')->name('supervision.')->group(function () {
    Route::get('/management', [SupervisionController::class, 'surveyManagement'])->name('management');
    Route::get('/surveys/{surveyId}/results', [SupervisionController::class, 'surveyResults'])->name('survey.results');
    Route::get('/performance', [SupervisionController::class, 'performance'])->name('performance');
});

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication Routes (these will be handled by Laravel Breeze)
require __DIR__.'/auth.php';