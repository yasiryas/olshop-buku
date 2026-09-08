<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductTransactionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');
Route::get('/category/{category:id}', [FrontController::class, 'category'])->name('front.product.category');
Route::get('/product/{product:slug}', [FrontController::class, 'productDetails'])->name('front.product.details');
Route::get('/product', [FrontController::class, 'product'])->name('front.product');
Route::get('/blog', [FrontController::class, 'blog'])->name('front.blog');
Route::get('/article/{article:slug}', [FrontController::class, 'article'])->name('front.article.details');
Route::get('/about', [FrontController::class, 'about'])->name('front.about');
Route::get('/contact', [FrontController::class, 'contact'])->name('front.contact');
Route::get('/search-products', [FrontController::class, 'searchProduct'])->name('front.search.ajax');
Route::get('/search/articles', [FrontController::class, 'searchArticle'])->name('front.search.article.ajax');

Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified', 'role:owner|admin|penulis'])
        ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route::resource('carts', CartController::class)->middleware('role:buyer');
    Route::resource('carts', CartController::class)->middleware('role:buyer|penulis')->except(['store', 'create', 'edit']);
    Route::post('/cart/add/{product_id}', [CartController::class, 'store'])
        ->middleware('role:buyer|penulis')
        ->name('carts.add');

    Route::resource('product_transactions', ProductTransactionController::class)
        ->middleware('role:admin|buyer|penulis');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', ProductController::class)->middleware('role:admin');
        Route::resource('categories', CategoryController::class)->middleware('role:admin');
        Route::resource('articles', ArticleController::class)->middleware('role:admin|penulis');
    });

    Route::prefix('admin/stocks')->name('stocks.')->middleware('role:admin')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/history', [StockController::class, 'allHistory'])->name('allHistory');
        Route::post('/{product}/update', [StockController::class, 'update'])->name('update');
        Route::get('/{product}/history', [StockController::class, 'history'])->name('history');
    });
});

require __DIR__ . '/auth.php';
