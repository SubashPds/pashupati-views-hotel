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
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

// ── Public frontend ───────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/enquire', [HomeController::class, 'enquire'])->name('enquire');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Admin (superadmin protected) ──────────────────────────────────────────────
Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {

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

    // Enquiries
    Route::get('/enquiries',                [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}',      [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::patch('/enquiries/{enquiry}/status', [EnquiryController::class, 'updateStatus'])->name('enquiries.update-status');
    Route::delete('/enquiries/{enquiry}',   [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

    // Site Settings
    Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
