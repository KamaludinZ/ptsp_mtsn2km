<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('questions', [SurveyController::class, 'createQuestion'])->name('questions.create');
    Route::get('questions/{id}', [SurveyController::class, 'getQuestion'])->name('questions.get');
    Route::put('questions/{id}', [SurveyController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('questions/{id}', [SurveyController::class, 'deleteQuestion'])->name('questions.delete');

    // Unsurs
    Route::get('unsurs', [SurveyController::class, 'getUnsurs'])->name('unsurs.index');
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});