<?php

use App\Http\Controllers\FamilyTreeController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Landing page ──
Route::get('/', fn() => view('welcome'))->name('home');

// ── Family tree (public to view, auth to add/delete) ──
Route::get('/family-tree', [FamilyTreeController::class, 'index'])->name('family-tree');
Route::middleware('auth')->group(function () {
    Route::post('/family-tree', [FamilyTreeController::class, 'store'])->name('family-tree.store');
    Route::patch('/family-tree/{familyMember}', [FamilyTreeController::class, 'update'])->name('family-tree.update');
    Route::delete('/family-tree/{familyMember}', [FamilyTreeController::class, 'destroy'])->name('family-tree.destroy');
});

// ── Game (public) ──
Route::get('/game', [GameController::class, 'index'])->name('game');

// ── Dashboard (auth required) ──
Route::get('/dashboard', fn() => view('dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

// ── Profile (auth required) ──
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
