<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\CorrectionLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\FileDownloadController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VersionController;
use App\Http\Controllers\ApiDataController;
use App\Http\Controllers\CorrectionSubmissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAssetController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\RosterTemplateDownloadController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inscripciones');
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->middleware(['auth', 'admin'])->name('dashboard');

Route::get('/inscripciones', [PublicRegistrationController::class, 'index'])->name('public.inscripciones');
Route::post('/inscripciones/continuar', [PublicRegistrationController::class, 'selectDivision'])
    ->name('public.inscripciones.continuar');
Route::get('/assets/logo-institucional', [PublicAssetController::class, 'institutionalLogo'])
    ->name('public.assets.logo');

Route::get('/inscripcion', [PublicRegistrationController::class, 'fallback'])->name('public.inscripcion.fallback');
Route::get('/inscripcion/{season}/{division}', [PublicRegistrationController::class, 'create'])
    ->name('public.inscripcion.filtered');
Route::post('/inscripcion/{season}/{division}', [PublicRegistrationController::class, 'store'])
    ->middleware('throttle:public-submissions')
    ->name('public.inscripcion.store');

Route::get('/correcciones/{year}/{division}/{club}/{token}', [CorrectionSubmissionController::class, 'show'])
    ->name('public.correcciones.show');
Route::post('/correcciones/{year}/{division}/{club}/{token}', [CorrectionSubmissionController::class, 'store'])
    ->middleware('throttle:correction-submissions')
    ->name('public.correcciones.store');

Route::get('/plantilla-nomina-jugadores', [RosterTemplateDownloadController::class, 'publicDownload'])
    ->name('public.roster-template.download');

Route::prefix('api')->middleware('throttle:public-api')->group(function (): void {
    Route::get('/seasons/{season}/divisions', [ApiDataController::class, 'divisions']);
    Route::get('/divisions/{division}/clubs', [ApiDataController::class, 'clubs']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('seasons', SeasonController::class)->except(['show']);
    Route::resource('divisions', DivisionController::class)->except(['show']);
    Route::resource('clubs', ClubController::class)->except(['show']);

    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::patch('/submissions/{submission}/status', [SubmissionController::class, 'updateStatus'])
        ->name('submissions.update-status');
    Route::patch('/submissions/{submission}/payment-status', [SubmissionController::class, 'updatePaymentStatus'])
        ->name('submissions.payment-status');
    Route::patch('/submissions/{submission}/extra', [SubmissionController::class, 'allowExtraSubmission'])
        ->name('submissions.extra');
    Route::patch('/submissions/{submission}/versions/{version}', [SubmissionController::class, 'updateVersionStatus'])
        ->name('submissions.versions.status');
    Route::delete('/submissions/{submission}/versions/{version}', [SubmissionController::class, 'destroyVersion'])
        ->name('submissions.versions.destroy');

    Route::get('/versions', [VersionController::class, 'index'])->name('versions.index');
    Route::patch('/versions/{version}/status', [VersionController::class, 'updateStatus'])
        ->name('versions.update-status');
    Route::delete('/versions/{version}', [VersionController::class, 'destroy'])
        ->name('versions.destroy');

    Route::get('/corrections', [CorrectionLinkController::class, 'index'])->name('corrections.index');
    Route::post('/corrections', [CorrectionLinkController::class, 'store'])->name('corrections.store');
    Route::patch('/corrections/{correctionLink}/toggle', [CorrectionLinkController::class, 'toggle'])->name('corrections.toggle');
    Route::patch('/corrections/{correctionLink}/regenerate', [CorrectionLinkController::class, 'regenerate'])->name('corrections.regenerate');

    Route::get('/download/version/{version}/{type}', FileDownloadController::class)->name('files.download');

    Route::get('/pagos', [PaymentController::class, 'index'])->name('pagos.index');
    Route::patch('/pagos/{submission}/status', [PaymentController::class, 'updateStatus'])->name('pagos.status');
    Route::get('/pagos/export', [PaymentController::class, 'export'])->name('pagos.export');
    Route::get('/historial', [HistoryController::class, 'index'])->name('historial.index');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/configuracion', [SettingController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [SettingController::class, 'update'])->name('configuracion.update');
    Route::get('/configuracion/plantilla-nomina-jugadores', [RosterTemplateDownloadController::class, 'adminDownload'])
        ->name('configuracion.roster-template.download');
    Route::resource('users', UserController::class)->except(['show']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
