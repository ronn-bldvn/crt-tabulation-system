<?php

use App\Http\Controllers\Admin\ContestantController;
use App\Http\Controllers\Admin\CriterionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\JudgeController;
use App\Http\Controllers\Admin\SegmentController;
use App\Http\Controllers\Admin\TabulationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Judge\ScoringController;
use App\Models\Event;
use App\Models\Segment;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SpecialAwardController;

Route::get('/', fn() => redirect('/login'));

// --- Auth ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::put('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');
});

// --- Admin ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/toggle-lock', [EventController::class, 'toggleLock'])->name('events.toggle-lock');

    Route::get('/events/{event}/segments', function (Event $event) {
        return redirect()->route('admin.events.show', $event);
    })->name('segments.index');

    Route::post('/events/{event}/segments', [SegmentController::class, 'store'])->name('segments.store');
    Route::put('/events/{event}/segments/{segment}', [SegmentController::class, 'update'])->name('segments.update');
    Route::delete('/events/{event}/segments/{segment}', [SegmentController::class, 'destroy'])->name('segments.destroy');
    Route::post('/events/{event}/segments/{segment}/toggle-lock', [SegmentController::class, 'toggleLock'])->name('segments.toggle-lock');

    Route::get('/events/{event}/segments/{segment}/criteria', function (Event $event, Segment $segment) {
        abort_unless($segment->event_id === $event->id, 404);
        return redirect()->route('admin.events.show', $event);
    })->name('criteria.index');

    Route::post('/events/{event}/segments/{segment}/criteria', [CriterionController::class, 'store'])->name('criteria.store');
    Route::put('/events/{event}/segments/{segment}/criteria/{criterion}', [CriterionController::class, 'update'])->name('criteria.update');
    Route::delete('/events/{event}/segments/{segment}/criteria/{criterion}', [CriterionController::class, 'destroy'])->name('criteria.destroy');

    Route::get('/events/{event}/contestants', function (Event $event) {
        return redirect()->route('admin.events.show', $event);
    })->name('contestants.index');

    Route::post('/events/{event}/contestants', [ContestantController::class, 'store'])->name('contestants.store');
    Route::put('/events/{event}/contestants/{contestant}', [ContestantController::class, 'update'])->name('contestants.update');
    Route::delete('/events/{event}/contestants/{contestant}', [ContestantController::class, 'destroy'])->name('contestants.destroy');

    Route::get('/judges', [JudgeController::class, 'index'])->name('judges.index');
    Route::post('/judges', [JudgeController::class, 'store'])->name('judges.store');
    Route::delete('/judges/{judge}', [JudgeController::class, 'destroy'])->name('judges.destroy');
    Route::post('/events/{event}/judges/assign', [JudgeController::class, 'assign'])->name('judges.assign');
    Route::delete('/events/{event}/judges/{judge}', [JudgeController::class, 'unassign'])->name('judges.unassign');

    Route::get('/events/{event}/results', [TabulationController::class, 'show'])->name('tabulation.show');
    Route::get('/events/{event}/print/results', [TabulationController::class, 'printResults'])->name('print.results');
    Route::get('/events/{event}/print/breakdown', [TabulationController::class, 'printBreakdown'])->name('print.breakdown');
    Route::get('/events/{event}/print/scoresheet/{judge}', [TabulationController::class, 'printScoresheet'])->name('print.scoresheet');
    Route::get('/events/{event}/print/certificate/{contestant}', [TabulationController::class, 'printCertificate'])->name('print.certificate');
    Route::get('/events/{event}/special-awards/results', [TabulationController::class, 'specialAwardsResults'])->name('special-awards.results');
    Route::get('/events/{event}/print/special-awards', [TabulationController::class, 'printSpecialAwards'])->name('print.special-awards');

    Route::get('/events/{event}/print/special-awards/breakdown', [TabulationController::class, 'printSpecialAwardsBreakdown'])
        ->name('print.special-awards.breakdown'); // admin.print.special-awards.breakdown

    Route::post(
        '/events/{event}/special-awards',
        [SpecialAwardController::class, 'store']
    )->name('special-awards.store');

    Route::put(
        '/events/{event}/special-awards/{award}',
        [SpecialAwardController::class, 'update']
    )->name('special-awards.update');

    Route::delete(
        '/events/{event}/special-awards/{award}',
        [SpecialAwardController::class, 'destroy']
    )->name('special-awards.destroy');

    Route::post(
        '/events/{event}/special-awards/{award}/winner',
        [SpecialAwardController::class, 'winner']
    )->name('special-awards.winner');

    Route::post(
        '/events/{event}/special-awards/{award}/contestants/{contestant}/photos',
        [SpecialAwardController::class, 'uploadPhotos']
    )->name('special-awards.photos.store');

    Route::delete(
        '/events/{event}/special-awards/{award}/photos/{photo}',
        [SpecialAwardController::class, 'destroyPhoto']
    )->name('special-awards.photos.destroy');
});

Route::middleware(['auth', 'judge'])
    ->prefix('judge')
    ->name('judge.')
    ->group(function () {

        // Judge events
        Route::get('/events', [ScoringController::class, 'events'])
            ->name('events');

        // View event segments
        Route::get('/events/{event}/segments', [ScoringController::class, 'segments'])
            ->name('segments');

        // View contestants for a specific segment
        Route::get('/events/{event}/segments/{segment}/contestants', [ScoringController::class, 'contestants'])
            ->name('contestants');

        // Score a specific contestant
        Route::get('/events/{event}/segments/{segment}/contestants/{contestant}/score', [ScoringController::class, 'score'])
            ->name('score');

        // Save contestant score
        Route::post('/events/{event}/segments/{segment}/contestants/{contestant}/score', [ScoringController::class, 'store'])
            ->name('score.store');

        // Judge event dashboard
        Route::get('/events/{event}', [ScoringController::class, 'show'])
            ->name('event');

        // Special Awards
        Route::get(
            '/events/{event}/special-awards',
            [ScoringController::class, 'specialAwards']
        )->name('special-awards');

        Route::get(
            '/events/{event}/special-awards/{award}/contestants/{contestant}',
            [ScoringController::class, 'specialAwardScore']
        )->name('special-award.score');

        Route::post(
            '/events/{event}/special-awards/{award}/contestants/{contestant}',
            [ScoringController::class, 'storeSpecialAwardScore']
        )->name('special-award.score.store');

        Route::get(
            '/events/{event}/special-awards/{award}/contestants',
            [ScoringController::class, 'specialAwardContestants']
        )->name('special-award.contestants');
    });
