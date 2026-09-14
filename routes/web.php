<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProductTransactionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductReturnController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\NotificationController;

Route::get('/sw.js', fn () => Response::file(resource_path('pwa/sw.js'), [
    'Content-Type' => 'application/javascript',
    'Cache-Control' => 'no-cache, no-store, must-revalidate',
]))->name('sw');

Route::get('/manifest.json', fn () => Response::file(resource_path('pwa/manifest.json'), [
    'Content-Type' => 'application/manifest+json',
    'Cache-Control' => 'no-cache, no-store, must-revalidate',
]))->name('manifest');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');
Route::get('/category/{category:id}', [FrontController::class, 'category'])->name('front.product.category');
Route::get('/product/{product:slug}', [FrontController::class, 'productDetails'])->name('front.product.details');
Route::get('/product', [FrontController::class, 'product'])->name('front.product');
Route::get('/blog', [FrontController::class, 'blog'])->name('front.blog');
Route::get('/article/{article:slug}', [FrontController::class, 'article'])->name('front.article.details');
Route::get('/about', [FrontController::class, 'about'])->name('front.about');
Route::get('/contact', [FrontController::class, 'contact'])->name('front.contact');
Route::get('/search-products', [FrontController::class, 'searchProduct'])->name('front.search.ajax')
    ->middleware('throttle:30,1');
Route::get('/search/articles', [FrontController::class, 'searchArticle'])->name('front.search.article.ajax')
    ->middleware('throttle:30,1');

Route::get('/session/keep-alive', fn () => Response::noContent())->name('session.keep-alive');

Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified', 'role:owner|admin|penulis'])
        ->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('carts', CartController::class)->middleware('role:buyer')->except(['store', 'create', 'edit']);
    Route::post('/cart/add/{product_id}', [CartController::class, 'store'])
        ->middleware('role:buyer')
        ->name('carts.add');
    Route::get('/cart/rates', [CartController::class, 'rates'])
        ->middleware('role:buyer')
        ->name('carts.rates');
    Route::get('/cart/locations', [CartController::class, 'locations'])
        ->middleware('role:buyer')
        ->name('carts.locations');

    Route::resource('product_transactions', ProductTransactionController::class)
        ->middleware('role:owner|admin|buyer')
        ->only(['index', 'show', 'store', 'destroy']);

    Route::get('product_transactions/{productTransaction}/preview', [ProductTransactionController::class, 'preview'])
        ->middleware('role:owner|admin')
        ->name('product_transactions.preview');

    Route::post('product_transactions/{productTransaction}/returns', [ProductReturnController::class, 'store'])
        ->middleware('role:buyer')
        ->name('product_returns.store');

    Route::post('product_transactions/{productTransaction}/proof', [ProductTransactionController::class, 'uploadProof'])
        ->middleware('role:buyer')
        ->name('product_transactions.proof');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', ProductController::class)->middleware('permission:manage products');
        Route::resource('categories', CategoryController::class)->middleware('permission:manage categories');
        Route::resource('articles', ArticleController::class)->middleware('permission:manage articles');

        Route::get('products/{product}/edit-data', [ProductController::class, 'editData'])
            ->name('products.edit-data')->middleware('permission:manage products');
        Route::get('categories/{category}/edit-data', [CategoryController::class, 'editData'])
            ->name('categories.edit-data')->middleware('permission:manage categories');
        Route::get('articles/{article}/edit-data', [ArticleController::class, 'editData'])
            ->name('articles.edit-data')->middleware('permission:manage articles');

        Route::resource('staff', StaffController::class)->only(['index', 'store'])->middleware('permission:manage staff');
        Route::post('staff/{user}/toggle', [StaffController::class, 'toggleActive'])->name('staff.toggle')->middleware('permission:manage staff');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:view reports');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export')->middleware('permission:view reports');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:manage customers');
        Route::get('customers/{user}', [CustomerController::class, 'show'])->name('customers.show')->middleware('permission:manage customers');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit')->middleware('permission:manage settings');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:manage settings');

        Route::post('orders/{productTransaction}/approve', [ProductTransactionController::class, 'approve'])->name('orders.approve')
            ->middleware('permission:process orders');
        Route::post('orders/{productTransaction}/ship', [ProductTransactionController::class, 'ship'])->name('orders.ship')
            ->middleware('permission:process orders');
        Route::post('orders/{productTransaction}/complete', [ProductTransactionController::class, 'complete'])->name('orders.complete')
            ->middleware('permission:process orders');
        Route::post('orders/{productTransaction}/reject', [ProductTransactionController::class, 'reject'])->name('orders.reject')
            ->middleware('permission:process orders');

        Route::get('returns', [ProductReturnController::class, 'index'])->name('returns.index')
            ->middleware('permission:process returns');
        Route::post('returns/{productReturn}/approve', [ProductReturnController::class, 'approve'])->name('returns.approve')
            ->middleware('permission:process returns');
        Route::post('returns/{productReturn}/reject', [ProductReturnController::class, 'reject'])->name('returns.reject')
            ->middleware('permission:process returns');
    });

    Route::prefix('admin/stocks')->name('stocks.')->middleware('permission:manage stocks')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/history', [StockController::class, 'allHistory'])->name('allHistory');
        Route::post('/{product}/update', [StockController::class, 'update'])->name('update');
        Route::get('/{product}/history', [StockController::class, 'history'])->name('history');
    });
});

require __DIR__ . '/auth.php';