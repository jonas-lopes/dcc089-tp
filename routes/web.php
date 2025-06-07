<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transaction.create');
Route::post('/transactions', [TransactionController::class, 'store'])->name('transaction.store');
Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transaction.edit');
Route::patch('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transaction.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transaction.destroy');
