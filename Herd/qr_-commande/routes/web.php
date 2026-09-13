<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as KitchenController;
use App\Http\Controllers\Admin\TableController;

Route::redirect('/', '/menu')->name('home');

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return redirect()->route('admin.items.index');
})->name('dashboard');

Route::get('/menu', [MenuController::class, 'show'])->name('menu');
Route::get('/menu/table/{table:code}', [MenuController::class, 'show'])->name('menu.table');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/kitchen', [KitchenController::class, 'index'])->name('orders.index');
    Route::patch('/kitchen/{order}/advance', [KitchenController::class, 'advance'])->name('orders.advance');
    Route::get('/history', [KitchenController::class, 'history'])->name('orders.history');

    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::get('/tables/create', [TableController::class, 'create'])->name('tables.create');
    Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');
});

require __DIR__.'/settings.php';