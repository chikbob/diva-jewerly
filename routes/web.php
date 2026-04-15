<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LivenessCheckController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\OrderController;
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

// Главная страница - отображаем категории
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/live', LivenessCheckController::class)->name('health.live');
Route::get('/ready', HealthCheckController::class)->name('health.ready');
Route::get('/up', HealthCheckController::class)->name('health.up');
Route::get('/metrics', MetricsController::class)
    ->middleware(\App\Http\Middleware\EnsureMetricsToken::class)
    ->name('metrics.index');

Route::get('/contacts', function () {
    return Inertia::render('Contacts/Index');
});

// Страница каталога - фильтрация товаров
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');

Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
