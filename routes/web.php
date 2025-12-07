<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductTransactionController;

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');
Route::get('/category/{category:id}', [FrontController::class, 'category'])->name('front.product.category');
// Route::get('/details/{product:slug}', [FrontController::class, 'details'])->name('front.product.details');
Route::get('/product/{product:slug}', [FrontController::class, 'productDetails'])->name('front.product.details');
Route::get('/product', [FrontController::class, 'product'])->name('front.product');
Route::get('/blog', [FrontController::class, 'blog'])->name('front.blog');
Route::get('/article/{article:slug}', [FrontController::class, 'article'])->name('front.article.details');
Route::get('/search/article', [FrontController::class, 'searchArticle'])->name('front.search.article');
Route::get('/abut', [FrontController::class, 'about'])->name('front.about');
Route::get('/contact', [FrontController::class, 'contact'])->name('front.contact');
Route::get('/search-products', [FrontController::class, 'searchProduct'])->name('front.search.ajax');
Route::get('/search-articles', [FrontController::class, 'searchArticle'])
    ->name('front.search.article.ajax');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route::resource('carts', CartController::class)->middleware('role:buyer');
    // Route::post('/cart/add/{product_id}', [CartController::class, 'store'])->middleware('role:buyer')->name('carts.store');
    Route::resource('carts', CartController::class)->middleware('role:buyer|penulis');
    Route::post('/cart/add/{product_id}', [CartController::class, 'store'])->middleware('role:buyer')->name('carts.add');

    Route::resource('product_transactions', ProductTransactionController::class)->middleware('role:owner|buyer|admin');

    // Route::resource('articles', ArticleController::class)->middleware('role:owner|admin|penulis');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', ProductController::class)->middleware('role:owner|admin');
        Route::resource('categories', CategoryController::class)->middleware('role:owner|admin');
        Route::resource('articles', ArticleController::class)->middleware('role:owner|admin|penulis');
    });

    Route::prefix('admin/stocks')->name('stocks.')->middleware('role:owner|admin')->group(function () {
        Route::get('/', [App\Http\Controllers\StockController::class, 'index'])->name('index');
        Route::get('/{product}', [App\Http\Controllers\StockController::class, 'show'])->name('show');
        Route::post('/{product}/update', [App\Http\Controllers\StockController::class, 'update'])->name('update');
        Route::get('/{product}/history', [App\Http\Controllers\StockController::class, 'history'])->name('history');
    });
});

require __DIR__ . '/auth.php';
