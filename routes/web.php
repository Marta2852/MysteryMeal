<?php

use App\Http\Controllers\ScoreController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;

Route::get('/', [RecipeController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/recipes/search', [RecipeController::class, 'index']);
Route::post('/recipes/add', [RecipeController::class, 'addProduct']);
Route::post('/recipes/remove', [RecipeController::class, 'removeProduct']);
Route::post('/recipes/search', [RecipeController::class, 'search']);

Route::get('/minigame', function () {
    return view('minigame/minigame');
})->middleware(['auth', 'verified']);
Route::get("/minigame/gameover", [ScoreController::class, 'gameover'])->middleware(['auth', 'verified']);

Route::post('/scores', [ScoreController::class, 'store'])->name('scores.store');
Route::get('/scores', [ScoreController::class, 'index'])->name('scores.index');

require __DIR__.'/auth.php';