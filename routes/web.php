<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController; 
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;   


Route::get('/', function () {
    return view('welcome');
});

// This single line creates all 7 CRUD routes for you
Route::resource('employees', EmployeeController::class);
Route::resource('departments', DepartmentController::class); 
Route::resource('divisions', DivisionController::class);  