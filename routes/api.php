<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by bootstrap/app.php and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('layanan')->group(function () {
    Route::get('/ringkasan-hari-ini', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'today'])->name('api.layanan.ringkasan-hari-ini');
    Route::get('/tren', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'trend'])->name('api.layanan.tren');
    Route::get('/laporan-bulanan', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'month'])->name('api.layanan.laporan-bulanan');
    Route::get('/kondisi', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'condition'])->name('api.layanan.kondisi');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/riwayat', [\App\Http\Controllers\Api\TicketHistoryController::class, 'show'])->name('api.tiket.riwayat');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/disposisi', [\App\Http\Controllers\Api\TicketHistoryController::class, 'dispositions'])->name('api.tiket.disposisi');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/berkas', [\App\Http\Controllers\Api\TicketHistoryController::class, 'documents'])->name('api.tiket.berkas');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/riwayat', [\App\Http\Controllers\Api\TicketHistoryController::class, 'index'])->name('api.riwayat');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/riwayat/ekspor', [\App\Http\Controllers\Api\TicketHistoryController::class, 'export'])->name('api.riwayat.ekspor');

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('pengaturan-disposisi')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ServiceDispositionController::class, 'index'])->name('api.pengaturan-disposisi.index');
    Route::get('/{service:slug}', [\App\Http\Controllers\Api\ServiceDispositionController::class, 'show'])->name('api.pengaturan-disposisi.show');
    Route::put('/{service:slug}', [\App\Http\Controllers\Api\ServiceDispositionController::class, 'update'])->name('api.pengaturan-disposisi.update');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('disposisi')->group(function () {
    Route::post('/riwayat/{disposition}/tanda-tangan', [\App\Http\Controllers\Api\DispositionController::class, 'uploadSignature'])->name('api.disposisi.tanda-tangan');
    Route::get('/antrean', [\App\Http\Controllers\Api\DispositionController::class, 'queue'])->name('api.disposisi.antrean');
    Route::post('/{ticket:ticket_number}', [\App\Http\Controllers\Api\DispositionController::class, 'decide'])->name('api.disposisi.putuskan');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('surat-keluar')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\SuratKeluarController::class, 'index'])->name('api.surat-keluar.index');
    Route::get('/ekspor', [\App\Http\Controllers\Api\SuratKeluarController::class, 'export'])->name('api.surat-keluar.ekspor');
    Route::post('/nomor', [\App\Http\Controllers\Api\SuratKeluarController::class, 'reserve'])->name('api.surat-keluar.nomor');
    Route::put('/{suratKeluar}', [\App\Http\Controllers\Api\SuratKeluarController::class, 'update'])->name('api.surat-keluar.update');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->get('/persuratan/pilihan', [\App\Http\Controllers\Api\PersuratanController::class, 'options'])->name('api.persuratan.pilihan');
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('persuratan/master')->name('api.persuratan.master.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\PersuratanController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\PersuratanController::class, 'store'])->name('store');
    Route::put('/{master}', [\App\Http\Controllers\Api\PersuratanController::class, 'update'])->name('update');
    Route::delete('/{master}', [\App\Http\Controllers\Api\PersuratanController::class, 'destroy'])->name('destroy');
});

// Public data: no sign-in, rate limited.
Route::middleware('throttle:60,1')->prefix('publik')->name('api.publik.')->group(function () {
    Route::get('/profil', [\App\Http\Controllers\Api\PublicController::class, 'profile'])->name('profil');
    Route::get('/layanan', [\App\Http\Controllers\Api\PublicController::class, 'services'])->name('layanan');
    Route::get('/layanan/{slug}', [\App\Http\Controllers\Api\PublicController::class, 'service'])->name('layanan.detail');
    Route::get('/pengumuman', [\App\Http\Controllers\Api\PublicController::class, 'announcements'])->name('pengumuman');
    Route::get('/pengumuman/{pengumuman}', [\App\Http\Controllers\Api\PublicController::class, 'announcement'])->whereNumber('pengumuman')->name('pengumuman.detail');
    Route::get('/faq', [\App\Http\Controllers\Api\PublicController::class, 'faq'])->name('faq');
    Route::get('/lacak/{nomor}', [\App\Http\Controllers\Api\PublicController::class, 'track'])->middleware('throttle:30,1')->name('lacak');
    Route::get('/buku-tamu/pilihan', [\App\Http\Controllers\Api\PublicController::class, 'guestBookOptions'])->name('buku-tamu.pilihan');
    Route::post('/buku-tamu', [\App\Http\Controllers\Api\PublicController::class, 'signGuestBook'])->middleware('throttle:public-forms')->name('buku-tamu');
    Route::post('/pengaduan', [\App\Http\Controllers\Api\PublicController::class, 'submitReport'])->middleware('throttle:public-forms')->name('pengaduan');
    Route::get('/pengaduan/{nomor}', [\App\Http\Controllers\Api\PublicController::class, 'trackReport'])->middleware('throttle:30,1')->name('pengaduan.lacak');
    Route::get('/survei', [\App\Http\Controllers\Api\SurveyController::class, 'structure'])->name('survei');
    Route::post('/survei', [\App\Http\Controllers\Api\SurveyController::class, 'submit'])->middleware('throttle:public-forms')->name('survei.kirim');
});

// Portal pemohon: the signed-in applicant's own requests.
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('permohonan')->name('api.permohonan.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ApplicantTicketController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\ApplicantTicketController::class, 'store'])->middleware('throttle:public-forms')->name('store');
    Route::get('/{ticket:ticket_number}', [\App\Http\Controllers\Api\ApplicantTicketController::class, 'show'])->name('show');
    Route::post('/{ticket:ticket_number}/berkas', [\App\Http\Controllers\Api\ApplicantTicketController::class, 'upload'])->middleware('throttle:public-forms')->name('berkas');
});

// Buku tamu at the counter
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('buku-tamu')->name('api.buku-tamu.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\VisitorController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\VisitorController::class, 'store'])->name('store');
    Route::get('/{visitor}', [\App\Http\Controllers\Api\VisitorController::class, 'show'])->whereNumber('visitor')->name('show');
    Route::post('/{visitor}/keluar', [\App\Http\Controllers\Api\VisitorController::class, 'checkOut'])->name('keluar');
    Route::patch('/{visitor}/catatan', [\App\Http\Controllers\Api\VisitorController::class, 'note'])->name('catatan');
});

// Pengaduan, saran & WBS for complaint handlers
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('pengaduan')->name('api.pengaduan.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ComplaintController::class, 'index'])->name('index');
    Route::patch('/{complaint}', [\App\Http\Controllers\Api\ComplaintController::class, 'update'])->name('update');
});

// Survei SKM & SPAK administration
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('survei/edisi')->name('api.survei.edisi.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\SurveyController::class, 'editions'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\SurveyController::class, 'storeEdition'])->name('store');
    Route::put('/{edition}', [\App\Http\Controllers\Api\SurveyController::class, 'updateEdition'])->name('update');
    Route::delete('/{edition}', [\App\Http\Controllers\Api\SurveyController::class, 'destroyEdition'])->name('destroy');
});
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('survei')->name('api.survei.')->group(function () {
    Route::get('/laporan', [\App\Http\Controllers\Api\SurveyController::class, 'report'])->name('laporan');
    Route::get('/unsur', [\App\Http\Controllers\Api\SurveyContentController::class, 'unsurIndex'])->name('unsur.index');
    Route::post('/unsur', [\App\Http\Controllers\Api\SurveyContentController::class, 'unsurStore'])->name('unsur.store');
    Route::put('/unsur/{unsur}', [\App\Http\Controllers\Api\SurveyContentController::class, 'unsurUpdate'])->name('unsur.update');
    Route::delete('/unsur/{unsur}', [\App\Http\Controllers\Api\SurveyContentController::class, 'unsurDestroy'])->name('unsur.destroy');
    Route::get('/pertanyaan', [\App\Http\Controllers\Api\SurveyContentController::class, 'questionIndex'])->name('pertanyaan.index');
    Route::post('/pertanyaan', [\App\Http\Controllers\Api\SurveyContentController::class, 'questionStore'])->name('pertanyaan.store');
    Route::put('/pertanyaan/{question}', [\App\Http\Controllers\Api\SurveyContentController::class, 'questionUpdate'])->name('pertanyaan.update');
    Route::delete('/pertanyaan/{question}', [\App\Http\Controllers\Api\SurveyContentController::class, 'questionDestroy'])->name('pertanyaan.destroy');
});

// Pengaturan integrasi Email & WhatsApp (admin)
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('integrasi')->name('api.integrasi.')->group(function () {
    Route::get('/{channel}', [\App\Http\Controllers\Api\IntegrationController::class, 'show'])->whereIn('channel', ['email', 'whatsapp'])->name('show');
    Route::put('/{channel}', [\App\Http\Controllers\Api\IntegrationController::class, 'update'])->whereIn('channel', ['email', 'whatsapp'])->name('update');
    Route::post('/{channel}/uji', [\App\Http\Controllers\Api\IntegrationController::class, 'test'])->whereIn('channel', ['email', 'whatsapp'])->middleware('throttle:10,1')->name('uji');
    Route::patch('/{channel}/aktif', [\App\Http\Controllers\Api\IntegrationController::class, 'toggle'])->whereIn('channel', ['email', 'whatsapp'])->name('aktif');
});

// Template notifikasi (admin)
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('template-notifikasi')->name('api.template-notifikasi.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\NotificationTemplateController::class, 'index'])->name('index');
    Route::get('/{template}', [\App\Http\Controllers\Api\NotificationTemplateController::class, 'show'])->name('show');
    Route::put('/{template}', [\App\Http\Controllers\Api\NotificationTemplateController::class, 'update'])->name('update');
    Route::patch('/{template}/aktif', [\App\Http\Controllers\Api\NotificationTemplateController::class, 'toggle'])->name('aktif');
});

// Monitoring Sistem (admin): guarded on the route as well as in the controller.
Route::middleware(['auth:sanctum', 'throttle:api', 'role:admin,sanctum'])->prefix('monitoring')->name('api.monitoring.')->group(function () {
    Route::get('/aplikasi', [\App\Http\Controllers\Api\MonitoringController::class, 'application'])->name('aplikasi');
    Route::get('/server', [\App\Http\Controllers\Api\MonitoringController::class, 'server'])->name('server');
    Route::get('/metrik', [\App\Http\Controllers\Api\MonitoringController::class, 'metrics'])->name('metrik');
    Route::get('/keamanan', [\App\Http\Controllers\Api\MonitoringController::class, 'security'])->name('keamanan');
    Route::get('/log', [\App\Http\Controllers\Api\MonitoringController::class, 'logs'])->name('log');
    Route::get('/pembaruan', [\App\Http\Controllers\Api\MonitoringController::class, 'updates'])->name('pembaruan');
    Route::get('/pembaruan/riwayat', [\App\Http\Controllers\Api\MonitoringController::class, 'updateHistory'])->name('pembaruan.riwayat');
    Route::get('/pembaruan/{update}', [\App\Http\Controllers\Api\MonitoringController::class, 'showUpdate'])->whereNumber('update')->name('pembaruan.detail');
    Route::post('/pembaruan/unggah', [\App\Http\Controllers\Api\MonitoringController::class, 'uploadUpdate'])->name('pembaruan.unggah');
    Route::post('/pembaruan/{update}/terapkan', [\App\Http\Controllers\Api\MonitoringController::class, 'applyUpdate'])->name('pembaruan.terapkan');
});
