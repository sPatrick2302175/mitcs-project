<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomHolidayController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsSuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Default Page Route
Route::get('/', function () {
    return view('auth.login');
});

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
    Route::get('/my-leave-history', [LeaveRequestController::class, 'myHistory'])->name('leave-requests.history');
    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::get('/leave-requests/{id}/pdf', [LeaveRequestController::class, 'generatePdf'])->name('leave-requests.pdf');
    Route::get('/leave-requests/{id}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
});

// --- SHARED ADMIN ROUTES ---
// Admin Officers (1), Super Admins (2), and Department Heads (3) can access these
Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::resource('divisions', DivisionController::class);
    Route::resource('employees', EmployeeController::class);

    // 🔐 SECURED: Moved inside the Admin group so standard employees are blocked entirely
    Route::put('/employees/{employee}/change-role', [EmployeeController::class, 'changeRole'])->name('employees.changeRole');

    // ==========================================
    // ADMIN / MANAGEMENT LEAVE ROUTES
    // ==========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/leave-requests', [LeaveRequestController::class, 'adminIndex'])->name('leave-requests.index');
        Route::get('/leave-requests/{id}/review', [LeaveRequestController::class, 'review'])->name('leave-requests.review');
        Route::post('/leave-requests/{id}/action', [LeaveRequestController::class, 'action'])->name('leave-requests.action');
        
        // Custom Holidays Management
        Route::resource('custom-holidays', CustomHolidayController::class)->except(['show']);
        Route::patch('/custom-holidays/{customHoliday}/toggle', [CustomHolidayController::class, 'toggleStatus'])->name('custom-holidays.toggle');
    });
});

// --- SUPER ADMIN ONLY ROUTES ---
// Only Super Admins (2) can access these. Others get a 403 error.
Route::middleware(['auth', IsSuperAdmin::class])->group(function () {
    Route::resource('departments', DepartmentController::class);
    
    // Manage users (only index, edit, and update)
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
});

// Breeze Authentication Routes
require __DIR__.'/auth.php';