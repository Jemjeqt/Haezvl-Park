<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\ExitController;
use App\Http\Controllers\HistoryController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Entry Gate
Route::get('entry', [EntryController::class, 'index'])->name('entry.index');
Route::post('entry', [EntryController::class, 'store'])->name('entry.store');
Route::get('entry/ticket/{ticket}', [EntryController::class, 'ticket'])->name('entry.ticket');

// Booth — QR scan dari HP langsung buka URL ini
Route::get('booth/{ticketCode}', [ExitController::class, 'booth'])->name('booth');

// Exit Gate (+ Payment merged)
Route::get('exit', [ExitController::class, 'index'])->name('exit.index');
Route::post('exit/lookup', [ExitController::class, 'lookup'])->name('exit.lookup');
Route::post('exit/pay', [ExitController::class, 'payAndExit'])->name('exit.pay');
Route::get('exit/receipt/{ticket}', [ExitController::class, 'receipt'])->name('exit.receipt');

// History
Route::get('history', [HistoryController::class, 'index'])->name('history.index');
