<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Default Page Route
Route::get('/', function () {
    return view('/auth/login');
});

// Breeze Dashboard Route (Only accessible if logged in)
Route::get('/dashboard', function () {
    if (Auth::user()->is_admin) {
        return view('admin.dashboard'); // Admins go here
    }
    
    return view('employee.dashboard'); // Regular employees go here
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// CRUD ROUTES w Admin middleware
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::resource('departments', App\Http\Controllers\DepartmentController::class);
    Route::resource('divisions', App\Http\Controllers\DivisionController::class);
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);
});

// Breeze Authentication Routes (Handles login, register, logout, etc.)
require __DIR__.'/auth.php';