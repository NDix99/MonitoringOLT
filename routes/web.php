<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        
        // OLT Management
        Route::resource('olts', \App\Http\Controllers\OltController::class);
        Route::get('/olts/{olt}/test-snmp', [\App\Http\Controllers\OltController::class, 'testSnmp'])->name('olts.test-snmp');
        Route::post('/olts/{olt}/test-ssh', [\App\Http\Controllers\OltController::class, 'testSsh'])->name('olts.test-ssh');
        Route::patch('/olts/{olt}/toggle', [\App\Http\Controllers\OltController::class, 'toggle'])->name('olts.toggle');
        Route::post('/olts/{olt}/fetch-onus', [\App\Http\Controllers\OltController::class, 'fetchOnus'])->name('olts.fetch-onus');
    });
});
