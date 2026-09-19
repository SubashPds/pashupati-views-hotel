<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\FaqController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\FaqController as FrontFaqController;

// ── Public frontend ───────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(\App\Http\Middleware\SelectDisplayCurrency::class);
Route::get('/blogs', [\App\Http\Controllers\BlogController::class, 'index'])->name('blogs.index')->middleware(\App\Http\Middleware\SelectDisplayCurrency::class);
Route::get('/blogs/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blogs.show')->middleware(\App\Http\Middleware\SelectDisplayCurrency::class);
Route::get('/faqs', [FrontFaqController::class, 'index'])->name('faqs.index');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('gallery');
// Route::get('/restaurant', [\App\Http\Controllers\RestaurantController::class, 'index'])->name('restaurant');
Route::post('/enquire', [HomeController::class, 'enquire'])->name('enquire');
Route::post('/currency', [\App\Http\Controllers\CurrencyPreferenceController::class, 'store'])->name('currency.store');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Dashboard protected by role permissions ──────────────────────────────────
Route::middleware(['admin.access', 'auth.session'])->prefix('admin')->name('admin.')->group(function () {

    Route::middleware('superadmin')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show', 'destroy']);
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    });

    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class)->except('show');

    Route::resource('hero-slides', \App\Http\Controllers\Admin\HeroSlideController::class)->except('show');
    Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class)->except('show');

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Rooms
    Route::get('/rooms',                    [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create',             [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms',                   [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit',        [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}',             [RoomController::class, 'update'])->name('rooms.update');
    Route::patch('/rooms/{room}/status',    [RoomController::class, 'toggleStatus'])->name('rooms.toggle-status');
    Route::delete('/rooms/{room}',          [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::delete('/room-images/{image}',   [RoomController::class, 'destroyImage'])->name('rooms.images.destroy');

    // Experiences
    Route::get('/experiences',              [ExperienceController::class, 'index'])->name('experiences.index');
    Route::get('/experiences/create',       [ExperienceController::class, 'create'])->name('experiences.create');
    Route::post('/experiences',             [ExperienceController::class, 'store'])->name('experiences.store');
    Route::get('/experiences/{experience}/edit',  [ExperienceController::class, 'edit'])->name('experiences.edit');
    Route::put('/experiences/{experience}',       [ExperienceController::class, 'update'])->name('experiences.update');
    Route::patch('/experiences/{experience}/status', [ExperienceController::class, 'toggleStatus'])->name('experiences.toggle-status');
    Route::delete('/experiences/{experience}',    [ExperienceController::class, 'destroy'])->name('experiences.destroy');

    // Gallery
    Route::get('/gallery',                  [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('/gallery',                 [GalleryController::class, 'store'])->name('gallery.store');
    Route::get('/gallery/{galleryItem}/edit', [GalleryController::class, 'edit'])->name('gallery.edit');
    Route::put('/gallery/{galleryItem}',    [GalleryController::class, 'update'])->name('gallery.update');
    Route::patch('/gallery/{galleryItem}/status', [GalleryController::class, 'toggleStatus'])->name('gallery.toggle-status');
    Route::delete('/gallery/{galleryItem}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

    // Services
    Route::get('/services',                 [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create',          [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services',                [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit',  [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}',       [ServiceController::class, 'update'])->name('services.update');
    Route::patch('/services/{service}/status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');
    Route::delete('/services/{service}',    [ServiceController::class, 'destroy'])->name('services.destroy');

    // Testimonials
    Route::get('/testimonials',             [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::get('/testimonials/create',      [TestimonialController::class, 'create'])->name('testimonials.create');
    Route::post('/testimonials',            [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::get('/testimonials/{testimonial}/edit',  [TestimonialController::class, 'edit'])->name('testimonials.edit');
    Route::put('/testimonials/{testimonial}',       [TestimonialController::class, 'update'])->name('testimonials.update');
    Route::patch('/testimonials/{testimonial}/status', [TestimonialController::class, 'toggleStatus'])->name('testimonials.toggle-status');
    Route::delete('/testimonials/{testimonial}',    [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

    // Packages
    Route::get('/packages',                    [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create',             [PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages',                   [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}/edit',     [PackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}',          [PackageController::class, 'update'])->name('packages.update');
    Route::patch('/packages/{package}/status', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
    Route::delete('/packages/{package}',       [PackageController::class, 'destroy'])->name('packages.destroy');
    Route::delete('/package-images/{image}',   [PackageController::class, 'destroyImage'])->name('packages.images.destroy');

    // Enquiries
    Route::get('/enquiries',                [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}',      [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::patch('/enquiries/{enquiry}/status', [EnquiryController::class, 'updateStatus'])->name('enquiries.update-status');
    Route::delete('/enquiries/{enquiry}',   [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

    // FAQs
    Route::get('/faqs',                    [FaqController::class, 'index'])->name('faqs.index');
    Route::get('/faqs/create',             [FaqController::class, 'create'])->name('faqs.create');
    Route::post('/faqs',                   [FaqController::class, 'store'])->name('faqs.store');
    Route::get('/faqs/{faq}/edit',         [FaqController::class, 'edit'])->name('faqs.edit');
    Route::put('/faqs/{faq}',              [FaqController::class, 'update'])->name('faqs.update');
    Route::patch('/faqs/{faq}/status',     [FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
    Route::delete('/faqs/{faq}',           [FaqController::class, 'destroy'])->name('faqs.destroy');

    // Site Settings
    Route::get('/restaurant', [\App\Http\Controllers\Admin\RestaurantController::class, 'index'])->name('restaurant.index');
    Route::put('/restaurant', [\App\Http\Controllers\Admin\RestaurantController::class, 'update'])->name('restaurant.update');

    Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
