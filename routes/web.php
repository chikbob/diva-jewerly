<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderReportController as AdminOrderReportController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::patch('/cart/quantity', [CartController::class, 'update'])->name('cart.update');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/payments/{paymentReference}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{paymentReference}/simulate/{status}', [PaymentController::class, 'simulate'])->name('payments.simulate');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('authenticate');

    Route::middleware(\App\Http\Middleware\EnsureBackofficeSession::class)->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/reports/orders', [AdminOrderReportController::class, 'index'])->name('reports.orders');
        Route::get('/reports/orders/export', [AdminOrderReportController::class, 'export'])->name('reports.orders.export');
        Route::get('/resources/{resource}', [AdminResourceController::class, 'index'])->name('resources.index');
        Route::get('/resources/{resource}/create', [AdminResourceController::class, 'create'])->name('resources.create');
        Route::post('/resources/{resource}', [AdminResourceController::class, 'store'])->name('resources.store');
        Route::get('/resources/{resource}/{record}/edit', [AdminResourceController::class, 'edit'])->name('resources.edit');
        Route::put('/resources/{resource}/{record}', [AdminResourceController::class, 'update'])->name('resources.update');
        Route::delete('/resources/{resource}/{record}', [AdminResourceController::class, 'destroy'])->name('resources.destroy');
    });
});

// Главная страница - отображаем категории
Route::get('/', [ProductController::class, 'home'])->name('home');

Route::get('/contacts', function () {
    return Inertia::render('Contacts/Index');
});

// Страница каталога - фильтрация товаров
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{product}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{product}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/receipt', [OrderReceiptController::class, 'show'])->name('orders.receipt.show');
    Route::get('/orders/{order}/receipt/download', [OrderReceiptController::class, 'download'])->name('orders.receipt.download');
    Route::post('/orders/{order}/repeat', [OrderController::class, 'repeat'])->name('orders.repeat');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
