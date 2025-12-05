<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Category routes
Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/create', [App\Http\Controllers\CategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{id}/edit', [App\Http\Controllers\CategoryController::class, 'edit'])->name('categories.edit');
Route::patch('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
