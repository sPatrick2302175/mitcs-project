<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsSuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Default Page Route
Route::get('/', function () {
    return view('auth.login');
});

// Breeze Dashboard Route (Only accessible if logged in)
Route::get('/dashboard', function () {
    // Explicitly check if the user is a Dept Admin or Super Admin (>= 1)
    if (Auth::user()->is_admin >= User::ROLE_DEPT_ADMIN) {
        return view('admin.dashboard'); // Both types of admins share this view now
    }
    
    return view('employee.dashboard'); // Regular employees go here
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- SHARED ADMIN ROUTES ---
// Both Department Admins (1) and Super Admins (2) can access these
Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::resource('divisions', DivisionController::class);
    Route::resource('employees', EmployeeController::class);
});

// --- SUPER ADMIN ONLY ROUTES ---
// Only Super Admins (2) can access these. Department Admins get a 403 error.
// Inside your routes/web.php file...

Route::middleware(['auth', IsSuperAdmin::class])->group(function () {
    Route::resource('departments', DepartmentController::class);
    
    // Add this line to manage users (we only need index, edit, and update)
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
});
// Breeze Authentication Routes (Handles login, register, logout, etc.)
require __DIR__.'/auth.php';