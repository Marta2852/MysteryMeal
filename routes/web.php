<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/minigame', function () {
    return view('minigame/minigame');
});
Route::get("/minigame/gameover", function(){
    return view ("minigame/gameover");
});
