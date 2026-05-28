<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

// 1. Welcome Page Route
Route::get('/', function () {
    return view('/auth/login');
});

// 2. Breeze Dashboard Route (Only accessible if logged in)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. YOUR CRUD ROUTES (Put them back right here!)
Route::resource('departments', DepartmentController::class);
Route::resource('divisions', DivisionController::class);
Route::resource('employees', EmployeeController::class);

// 5. Breeze Authentication Routes (Handles login, register, logout, etc.)
require __DIR__.'/auth.php';