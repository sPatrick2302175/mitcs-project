<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController; // <-- Added Leave Request Controller
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsSuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Default Page Route
Route::get('/', function () {
    return view('auth.login');
});

//  PASTE THIS IN ITS PLACE:
Route::get('/dashboard', [LeaveRequestController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::redirect('/leave-requests', '/dashboard')->name('leave-requests.index');
// Breeze Profile & Employee Leave Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==========================================
    // EMPLOYEE LEAVE ROUTES
    // ==========================================
    // Allows any authenticated user to view their balance, apply, and get PDFs
    Route::get('/my-leave-history', [LeaveRequestController::class, 'myHistory'])->name('leave-requests.history');
    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::get('/leave-requests/{id}/pdf', [LeaveRequestController::class, 'generatePdf'])->name('leave-requests.pdf');
});

// --- SHARED ADMIN ROUTES ---
// Both Department Admins (1) and Super Admins (2) can access these
Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::resource('divisions', DivisionController::class);
    Route::resource('employees', EmployeeController::class);

    // ==========================================
    // ADMIN / MANAGEMENT LEAVE ROUTES
    // ==========================================
    // Placed here so both Department Admins and Super Admins can review requests
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/leave-requests', [LeaveRequestController::class, 'adminIndex'])->name('leave-requests.index');
        Route::get('/leave-requests/{id}/review', [LeaveRequestController::class, 'review'])->name('leave-requests.review');
        Route::post('/leave-requests/{id}/action', [LeaveRequestController::class, 'action'])->name('leave-requests.action');
    });
});

// --- SUPER ADMIN ONLY ROUTES ---
// Only Super Admins (2) can access these. Department Admins get a 403 error.
Route::middleware(['auth', IsSuperAdmin::class])->group(function () {
    Route::resource('departments', DepartmentController::class);
    
    // Manage users (only index, edit, and update)
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
});

// Breeze Authentication Routes (Handles login, register, logout, etc.)
require __DIR__.'/auth.php';

Route::put('/employees/{employee}/change-role', [EmployeeController::class, 'changeRole'])->name('employees.changeRole');


