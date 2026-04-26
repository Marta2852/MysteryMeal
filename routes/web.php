<?php

use App\Http\Controllers\ScoreController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;

// Dashboard as homepage
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Recipe Routes
|--------------------------------------------------------------------------
*/
Route::get('/recipes/search', [RecipeController::class, 'index'])->name('recipes.index');
Route::post('/recipes/add', [RecipeController::class, 'addProduct'])->name('recipes.addProduct');
Route::post('/recipes/remove', [RecipeController::class, 'removeProduct'])->name('recipes.removeProduct');
Route::post('/recipes/search', [RecipeController::class, 'search'])->name('recipes.search');
Route::get('/recipes/results', [RecipeController::class, 'showResults'])->name('recipes.results');
Route::get('/recipes/favorite', [RecipeController::class, 'addFavorite'])->name('recipes.addFavorite');
Route::post('/recipes/remove-favorite', [RecipeController::class, 'removeFavorite'])->name('recipes.removeFavorite');
Route::get('/recipes/favorites', [RecipeController::class, 'favorites'])->name('recipes.favorites');
Route::get('/recipes/create', [RecipeController::class, 'create'])->name('recipes.create');
Route::post('/recipes/store', [RecipeController::class, 'store'])->name('recipes.store');

/*
|--------------------------------------------------------------------------
| Minigame Routes
|--------------------------------------------------------------------------
*/
Route::get('/minigame', function () {
    return view('minigame/minigame');
})->middleware(['auth', 'verified'])->name('minigame.index');

Route::get("/minigame/gameover", [ScoreController::class, 'gameover'])
    ->middleware(['auth', 'verified'])->name('minigame.gameover');

Route::post('/scores', [ScoreController::class, 'store'])->name('scores.store');
Route::get('/scores', [ScoreController::class, 'index'])->name('scores.index');

require __DIR__.'/auth.php';