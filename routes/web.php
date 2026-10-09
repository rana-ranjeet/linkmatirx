<?php

use App\Http\Controllers\Admin\WebsiteModerationController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WriterManagementController;
use App\Http\Controllers\Advertiser\WebsiteFavoriteController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\Publisher\WebsiteController;
use App\Http\Controllers\Publisher\DashboardController as PublisherDashboardController;
use App\Http\Controllers\Publisher\SupportController as PublisherSupportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{website}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/services', PublicPageController::class)->defaults('page', 'services')->name('services');
Route::get('/how-it-works', PublicPageController::class)->defaults('page', 'how-it-works')->name('how-it-works');
Route::get('/pricing', PublicPageController::class)->defaults('page', 'pricing')->name('pricing');
Route::get('/about', PublicPageController::class)->defaults('page', 'about')->name('about');
Route::get('/faqs', PublicPageController::class)->defaults('page', 'faqs')->name('faqs');
Route::get('/blog/sitemap.xml', [BlogController::class, 'sitemap'])->name('blog.sitemap');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nDisallow:\n\nSitemap: ".url('/blog/sitemap.xml')."\n",
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8'],
))->name('robots');
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
    Route::get('/dashboard', PublisherDashboardController::class)->name('dashboard');
    Route::get('/support', [PublisherSupportController::class, 'index'])->name('support.index');
    Route::post('/support', [PublisherSupportController::class, 'store'])->middleware('throttle:5,1')->name('support.store');
    Route::get('/websites', [WebsiteController::class, 'index'])->name('websites.index');
    Route::get('/websites/create', [WebsiteController::class, 'create'])->name('websites.create');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::get('/websites/{website}/edit', [WebsiteController::class, 'edit'])->name('websites.edit');
    Route::put('/websites/{website}', [WebsiteController::class, 'update'])->name('websites.update');
    Route::patch('/websites/{website}/availability', [WebsiteController::class, 'updateAvailability'])->name('websites.availability');
});

Route::middleware(['auth', 'active', 'verified', 'role:content_writer'])->prefix('writer')->name('writer.')->group(function () {
    Route::get('/dashboard', [ArticleController::class, 'writerDashboard'])->name('dashboard');
});

Route::middleware(['auth', 'active', 'verified', 'role:seo_manager'])->prefix('seo')->name('seo.')->group(function () {
    Route::get('/dashboard', [ArticleController::class, 'seoDashboard'])->name('dashboard');
});

Route::middleware(['auth', 'active', 'verified', 'role:admin,super_admin,seo_manager,content_writer'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::post('/articles/{article}/submit', [ArticleController::class, 'submit'])->name('articles.submit');
    Route::post('/articles/{article}/review', [ArticleController::class, 'review'])->name('articles.review')->middleware('role:admin,super_admin,seo_manager');
    Route::post('/articles/{article}/publish', [ArticleController::class, 'publish'])->name('articles.publish')->middleware('role:admin,super_admin');
});

Route::middleware(['auth', 'active', 'verified', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::get('/writers', [WriterManagementController::class, 'index'])->name('writers.index');
    Route::post('/writers', [WriterManagementController::class, 'store'])->name('writers.store');
    Route::patch('/writers/{user}/role', [WriterManagementController::class, 'updateRole'])->name('writers.role');
    Route::get('/websites', [WebsiteModerationController::class, 'index'])->name('websites.index');
    Route::patch('/websites/{website}/review', [WebsiteModerationController::class, 'update'])->name('websites.review');
    Route::get('/blog', [AdminBlogPostController::class, 'index'])->name('blog.index');
    Route::get('/blog/create', [AdminBlogPostController::class, 'create'])->name('blog.create');
    Route::post('/blog', [AdminBlogPostController::class, 'store'])->name('blog.store');
    Route::get('/blog/{post}/edit', [AdminBlogPostController::class, 'edit'])->name('blog.edit');
    Route::put('/blog/{post}', [AdminBlogPostController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{post}', [AdminBlogPostController::class, 'destroy'])->name('blog.destroy');
});

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
