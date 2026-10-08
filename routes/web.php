<?php

use App\Http\Controllers\Admin\WebsiteModerationController;
use App\Http\Controllers\Advertiser\WebsiteFavoriteController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\Publisher\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{website}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/services', PublicPageController::class)->defaults('page', 'services')->name('services');
Route::get('/how-it-works', PublicPageController::class)->defaults('page', 'how-it-works')->name('how-it-works');
Route::get('/pricing', PublicPageController::class)->defaults('page', 'pricing')->name('pricing');
Route::get('/about', PublicPageController::class)->defaults('page', 'about')->name('about');
Route::get('/faqs', PublicPageController::class)->defaults('page', 'faqs')->name('faqs');
Route::get('/blog', PublicPageController::class)->defaults('page', 'blog')->name('blog.index');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/privacy', PublicPageController::class)->defaults('page', 'privacy')->name('privacy');
Route::get('/terms', PublicPageController::class)->defaults('page', 'terms')->name('terms');
Route::get('/refund-policy', PublicPageController::class)->defaults('page', 'refund-policy')->name('refund-policy');
Route::get('/cookie-policy', PublicPageController::class)->defaults('page', 'cookie-policy')->name('cookie-policy');

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'active', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'active', 'verified', 'role:advertiser'])->prefix('advertiser')->name('advertiser.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/favorites', [WebsiteFavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{website}', [WebsiteFavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{website}', [WebsiteFavoriteController::class, 'destroy'])->name('favorites.destroy');
});

Route::middleware(['auth', 'active', 'verified', 'role:publisher'])->prefix('publisher')->name('publisher.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/websites', [WebsiteController::class, 'index'])->name('websites.index');
    Route::get('/websites/create', [WebsiteController::class, 'create'])->name('websites.create');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::get('/websites/{website}/edit', [WebsiteController::class, 'edit'])->name('websites.edit');
    Route::put('/websites/{website}', [WebsiteController::class, 'update'])->name('websites.update');
});

Route::middleware(['auth', 'active', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/websites', [WebsiteModerationController::class, 'index'])->name('websites.index');
    Route::patch('/websites/{website}/review', [WebsiteModerationController::class, 'update'])->name('websites.review');
});

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
