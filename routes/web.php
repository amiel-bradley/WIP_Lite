<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentHistoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('campaigns', CampaignController::class);
    Route::resource('assignments', AssignmentController::class);
    Route::resource('assignment-histories', AssignmentHistoryController::class)->only(['index', 'show', 'destroy']);

    Route::prefix('hierarchy-assignments')->group(function () {
        Route::get('/', [HierarchyAssignmentController::class, 'index'])->name('hierarchy-assignments.index');
        Route::post('/assign-cp', [HierarchyAssignmentController::class, 'assignCpToCampaign'])->name('hierarchy-assignments.assign-cp');
        Route::post('/assign-supervisor', [HierarchyAssignmentController::class, 'assignSupervisorToCp'])->name('hierarchy-assignments.assign-supervisor');
        Route::post('/assign-tc', [HierarchyAssignmentController::class, 'assignTcToSupervisor'])->name('hierarchy-assignments.assign-tc');
        Route::post('/{assignment}/unassign', [HierarchyAssignmentController::class, 'unassign'])->name('hierarchy-assignments.unassign');
        Route::get('/{assignment}', [HierarchyAssignmentController::class, 'show'])->name('hierarchy-assignments.show');
        Route::get('/campaign/{campaign}/hierarchy', [HierarchyAssignmentController::class, 'getHierarchy'])->name('hierarchy-assignments.hierarchy');
    });
});

require __DIR__.'/auth.php';