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
    Route::get('/laporan-bulanan/ekspor/{format}', \App\Http\Controllers\MonthlyReportExportController::class)->whereIn('format', ['pdf', 'xlsx'])->name('api.layanan.laporan-bulanan.ekspor');
    Route::get('/laporan-bulanan/rekap-layanan', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'serviceRecap'])->name('api.layanan.rekap-layanan');
    Route::get('/rekap-tahunan', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'yearRecap'])->name('api.layanan.rekap-tahunan');
    Route::get('/kondisi', [\App\Http\Controllers\Api\ServiceSummaryController::class, 'condition'])->name('api.layanan.kondisi');
});

Route::middleware(['auth:sanctum', 'throttle:api', 'izin:backoffice.access'])->get('/layanan-masuk', [\App\Http\Controllers\Api\IncomingServiceController::class, 'index'])->name('api.layanan-masuk.index');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket', [\App\Http\Controllers\Api\StaffTicketController::class, 'index'])->name('api.tiket.index');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/pilihan-filter', [\App\Http\Controllers\Api\StaffTicketController::class, 'filterOptions'])->name('api.tiket.pilihan-filter');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/riwayat-kategori', [\App\Http\Controllers\Api\StaffTicketController::class, 'categoryHistory'])->name('api.tiket.riwayat-kategori');
Route::middleware(['auth:sanctum', 'throttle:api'])->put('/tiket/{ticket:ticket_number}/kategori', [\App\Http\Controllers\Api\StaffTicketController::class, 'updateCategory'])->name('api.tiket.kategori');
Route::middleware(['auth:sanctum', 'throttle:api'])->patch('/tiket/{ticket:ticket_number}/status', [\App\Http\Controllers\Api\StaffTicketController::class, 'updateStatus'])->name('api.tiket.status');
Route::middleware(['auth:sanctum', 'throttle:api'])->put('/tiket/{ticket:ticket_number}/petugas', [\App\Http\Controllers\Api\StaffTicketController::class, 'assign'])->name('api.tiket.petugas');
Route::middleware(['auth:sanctum', 'throttle:api'])->post('/tiket/{ticket:ticket_number}/hasil', [\App\Http\Controllers\Api\StaffTicketController::class, 'uploadOutput'])->name('api.tiket.hasil');
Route::middleware(['auth:sanctum', 'throttle:api'])->post('/tiket/{ticket:ticket_number}/serah-terima', [\App\Http\Controllers\Api\StaffTicketController::class, 'handOver'])->name('api.tiket.serah-terima');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/catatan', [\App\Http\Controllers\Api\StaffTicketController::class, 'notes'])->name('api.tiket.catatan');
Route::middleware(['auth:sanctum', 'throttle:api'])->post('/tiket/{ticket:ticket_number}/catatan', [\App\Http\Controllers\Api\StaffTicketController::class, 'addNote'])->name('api.tiket.catatan.tambah');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}', [\App\Http\Controllers\Api\StaffTicketController::class, 'show'])->name('api.tiket.show');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/riwayat', [\App\Http\Controllers\Api\TicketHistoryController::class, 'show'])->name('api.tiket.riwayat');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/disposisi', [\App\Http\Controllers\Api\TicketHistoryController::class, 'dispositions'])->name('api.tiket.disposisi');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/tiket/{ticket:ticket_number}/berkas', [\App\Http\Controllers\Api\TicketHistoryController::class, 'documents'])->name('api.tiket.berkas');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/riwayat', [\App\Http\Controllers\Api\TicketHistoryController::class, 'index'])->name('api.riwayat');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/riwayat/ekspor', [\App\Http\Controllers\Api\TicketHistoryController::class, 'export'])->name('api.riwayat.ekspor');

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('unit-kerja')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\UnitStaffController::class, 'index'])->name('api.unit-kerja.index');
    Route::put('/{unit}', [\App\Http\Controllers\Api\UnitStaffController::class, 'update'])->name('api.unit-kerja.update');
});

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
    Route::get('/slider', [\App\Http\Controllers\Api\HeroSliderController::class, 'index'])->name('slider');
    Route::get('/layanan', [\App\Http\Controllers\Api\PublicController::class, 'services'])->name('layanan');
    Route::get('/layanan/{slug}', [\App\Http\Controllers\Api\PublicController::class, 'service'])->name('layanan.detail');
    Route::get('/layanan/{slug}/template', [\App\Http\Controllers\Api\PublicController::class, 'serviceTemplates'])->name('layanan.template');
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
    Route::get('/layanan', [\App\Http\Controllers\Api\ApplicantTicketController::class, 'services'])->name('layanan');
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
    Route::post('/{complaint}/rahasiakan', [\App\Http\Controllers\Api\ComplaintController::class, 'makeConfidential'])->name('rahasiakan');
});

// Pengingat survei: completed requests not rated yet (supervisors)
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/survei/belum-mengisi', [\App\Http\Controllers\Api\SurveyReminderController::class, 'index'])->name('api.survei.belum-mengisi');
Route::middleware(['auth:sanctum', 'throttle:api'])->post('/survei/pengingat', [\App\Http\Controllers\Api\SurveyReminderController::class, 'send'])->name('api.survei.pengingat');

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

// Pendaftaran walk-in di loket (front desk)
Route::middleware(['auth:sanctum', 'throttle:api'])->post('/loket/permohonan', [\App\Http\Controllers\Api\WalkInController::class, 'store'])->name('api.loket.permohonan');

// Riwayat pengiriman notifikasi (admin): filter, detail, kirim ulang yang gagal
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('riwayat-notifikasi')->name('api.riwayat-notifikasi.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\NotificationDeliveryController::class, 'index'])->name('index');
    Route::get('/{delivery}', [\App\Http\Controllers\Api\NotificationDeliveryController::class, 'show'])->whereNumber('delivery')->name('show');
    Route::post('/{delivery}/kirim-ulang', [\App\Http\Controllers\Api\NotificationDeliveryController::class, 'resend'])->whereNumber('delivery')->name('kirim-ulang');
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

// Katalog Layanan for staff: list and detail.
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/layanan-ptsp', [\App\Http\Controllers\Api\ServiceCatalogController::class, 'index'])->name('api.layanan-ptsp.index');
Route::middleware(['auth:sanctum', 'throttle:api'])->post('/layanan-ptsp', [\App\Http\Controllers\Api\ServiceCatalogController::class, 'store'])->name('api.layanan-ptsp.store');
Route::middleware(['auth:sanctum', 'throttle:api'])->patch('/layanan-ptsp/{service}/aktif', [\App\Http\Controllers\Api\ServiceCatalogController::class, 'setActive'])->whereNumber('service')->name('api.layanan-ptsp.aktif');
Route::middleware(['auth:sanctum', 'throttle:api'])->patch('/layanan-ptsp/{service}', [\App\Http\Controllers\Api\ServiceCatalogController::class, 'update'])->whereNumber('service')->name('api.layanan-ptsp.update');
Route::middleware(['auth:sanctum', 'throttle:api'])->get('/layanan-ptsp/{service}', [\App\Http\Controllers\Api\ServiceCatalogController::class, 'show'])->whereNumber('service')->name('api.layanan-ptsp.show');

// Template berkas layanan (admin): upload, replace the file, delete.
Route::middleware(['auth:sanctum', 'throttle:api', 'izin:peran:admin'])->prefix('layanan-ptsp/{service}/template')->name('api.layanan-ptsp.template.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ServiceTemplateController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\ServiceTemplateController::class, 'store'])->name('store');
    Route::get('/{template}', [\App\Http\Controllers\Api\ServiceTemplateController::class, 'show'])->name('show');
    Route::patch('/{template}', [\App\Http\Controllers\Api\ServiceTemplateController::class, 'update'])->name('update');
    Route::post('/{template}/berkas', [\App\Http\Controllers\Api\ServiceTemplateController::class, 'replaceFile'])->name('berkas');
    Route::delete('/{template}', [\App\Http\Controllers\Api\ServiceTemplateController::class, 'destroy'])->name('destroy');
});

// Notifikasi in-app of the signed-in user.
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('notifikasi')->name('api.notifikasi.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index'])->name('index');
    Route::get('/jumlah', [\App\Http\Controllers\Api\NotificationController::class, 'count'])->name('jumlah');
    Route::post('/dibaca', [\App\Http\Controllers\Api\NotificationController::class, 'markMany'])->name('dibaca');
    Route::patch('/{id}/dibaca', [\App\Http\Controllers\Api\NotificationController::class, 'markOne'])->whereUuid('id')->name('dibaca.satu');
});

// Masuk dan keluar (Sanctum token).
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/masuk', [\App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:20,1')->name('masuk');
    Route::post('/lupa-kata-sandi', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword'])->middleware('throttle:6,1')->name('lupa-kata-sandi');
    Route::post('/reset-kata-sandi', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('reset-kata-sandi');
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::get('/saya', [\App\Http\Controllers\Api\AuthController::class, 'me'])->name('saya');
        Route::post('/keluar', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->name('keluar');
        Route::post('/keluar-semua', [\App\Http\Controllers\Api\AuthController::class, 'logoutEverywhere'])->name('keluar-semua');
        Route::put('/kata-sandi', [\App\Http\Controllers\Api\AuthController::class, 'changePassword'])->name('kata-sandi');
    });
});

// Pengumuman (admin): every status, with filters; tayangkan/draf/akhiri as actions.
Route::middleware(['auth:sanctum', 'throttle:api', 'izin:peran:admin'])->prefix('pengumuman')->name('api.pengumuman.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\PengumumanController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\PengumumanController::class, 'store'])->name('store');
    Route::get('/{pengumuman}', [\App\Http\Controllers\Api\PengumumanController::class, 'show'])->whereNumber('pengumuman')->name('show');
    Route::patch('/{pengumuman}', [\App\Http\Controllers\Api\PengumumanController::class, 'update'])->whereNumber('pengumuman')->name('update');
    Route::delete('/{pengumuman}', [\App\Http\Controllers\Api\PengumumanController::class, 'destroy'])->whereNumber('pengumuman')->name('destroy');
    Route::post('/{pengumuman}/{action}', [\App\Http\Controllers\Api\PengumumanController::class, 'transition'])->whereNumber('pengumuman')->whereIn('action', ['tayangkan', 'draf', 'akhiri'])->name('aksi');
});

// FAQ (admin): CRUD plus ordering (whole list or one step up/down).
Route::middleware(['auth:sanctum', 'throttle:api', 'izin:peran:admin'])->prefix('faq')->name('api.faq.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\FaqController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\FaqController::class, 'store'])->name('store');
    Route::put('/urutan', [\App\Http\Controllers\Api\FaqController::class, 'reorder'])->name('urutan');
    Route::get('/{faq}', [\App\Http\Controllers\Api\FaqController::class, 'show'])->whereNumber('faq')->name('show');
    Route::patch('/{faq}', [\App\Http\Controllers\Api\FaqController::class, 'update'])->whereNumber('faq')->name('update');
    Route::delete('/{faq}', [\App\Http\Controllers\Api\FaqController::class, 'destroy'])->whereNumber('faq')->name('destroy');
    Route::post('/{faq}/{direction}', [\App\Http\Controllers\Api\FaqController::class, 'move'])->whereNumber('faq')->whereIn('direction', ['naik', 'turun'])->name('pindah');
});

// Peran aktif of the signed-in staff member: the roles they hold and the one in use.
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('peran-aktif')->name('api.peran-aktif.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ActiveRoleController::class, 'index'])->name('index');
    Route::put('/', [\App\Http\Controllers\Api\ActiveRoleController::class, 'update'])->name('update');
});

// Preferensi tampilan of the signed-in user (any account).
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('preferensi-tampilan')->name('api.preferensi-tampilan.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\DisplayPreferenceController::class, 'show'])->name('show');
    Route::patch('/', [\App\Http\Controllers\Api\DisplayPreferenceController::class, 'update'])->name('update');
});

// Pengaturan Aplikasi (admin): the Pengaturan screen's settings.
Route::middleware(['auth:sanctum', 'throttle:api', 'izin:peran:admin'])->prefix('pengaturan')->name('api.pengaturan.')->group(function () {
    Route::get('/profil', [\App\Http\Controllers\Api\SettingsController::class, 'profile'])->name('profil');
    Route::patch('/profil', [\App\Http\Controllers\Api\SettingsController::class, 'updateProfile'])->name('profil.update');
    Route::post('/profil/logo', [\App\Http\Controllers\Api\SettingsController::class, 'uploadLogo'])->name('profil.logo');
    Route::delete('/profil/logo', [\App\Http\Controllers\Api\SettingsController::class, 'deleteLogo'])->name('profil.logo.hapus');
    Route::get('/umum', [\App\Http\Controllers\Api\SettingsController::class, 'general'])->name('umum');
    Route::patch('/umum', [\App\Http\Controllers\Api\SettingsController::class, 'updateGeneral'])->name('umum.update');
});

// Pengguna (admin).
Route::middleware(['auth:sanctum', 'throttle:api', 'izin:peran:admin'])->prefix('pengguna')->name('api.pengguna.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Api\UserController::class, 'store'])->name('store');
    Route::get('/{user}', [\App\Http\Controllers\Api\UserController::class, 'show'])->whereNumber('user')->name('show');
    Route::patch('/{user}', [\App\Http\Controllers\Api\UserController::class, 'update'])->whereNumber('user')->name('update');
    Route::patch('/{user}/aktif', [\App\Http\Controllers\Api\UserController::class, 'setActive'])->whereNumber('user')->name('aktif');
    Route::post('/{user}/kirim-reset-kata-sandi', [\App\Http\Controllers\Api\UserController::class, 'sendPasswordReset'])->whereNumber('user')->name('reset-kata-sandi');
    Route::delete('/{user}', [\App\Http\Controllers\Api\UserController::class, 'destroy'])->whereNumber('user')->name('destroy');
});

// Peran dan izin (admin).
Route::middleware(['auth:sanctum', 'throttle:api', 'izin:peran:admin'])->prefix('peran')->name('api.peran.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\RoleController::class, 'index'])->name('index');
    Route::get('/izin', [\App\Http\Controllers\Api\RoleController::class, 'permissions'])->name('izin');
    Route::post('/', [\App\Http\Controllers\Api\RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [\App\Http\Controllers\Api\RoleController::class, 'show'])->whereNumber('role')->name('show');
    Route::patch('/{role}', [\App\Http\Controllers\Api\RoleController::class, 'update'])->whereNumber('role')->name('update');
    Route::delete('/{role}', [\App\Http\Controllers\Api\RoleController::class, 'destroy'])->whereNumber('role')->name('destroy');
});
