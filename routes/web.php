<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\Admin\AdminAdController;
use App\Http\Controllers\MyAdController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/ads', [AdController::class, 'index'])->name('ads.index');

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/ads/create', [AdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::post('/ads/{ad:slug}/phone', [AdController::class, 'phone'])
        ->middleware('throttle:20,1')->name('ads.phone');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/my-ads', [MyAdController::class, 'index'])->name('my-ads.index');
    Route::get('/my-ads/{ad}/edit', [MyAdController::class, 'edit'])->name('my-ads.edit');
    Route::put('/my-ads/{ad}', [MyAdController::class, 'update'])->name('my-ads.update');
    Route::patch('/my-ads/{ad}/sold', [MyAdController::class, 'markSold'])->name('my-ads.sold');
    Route::delete('/my-ads/{ad}', [MyAdController::class, 'destroy'])->name('my-ads.destroy');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/ads/{ad:slug}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('/ads/{ad:slug}/report', [ReportController::class, 'store'])
        ->middleware('throttle:10,1')->name('reports.store');

    Route::patch('/my-ads/{ad}/renew', [MyAdController::class, 'renew'])->name('my-ads.renew');
});
Route::get('/ads/{ad:slug}', [AdController::class, 'show'])->name('ads.show');

Route::middleware(['auth', 'verified', 'role:admin|moderator'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/ads', [AdminAdController::class, 'index'])->name('ads.index');

        Route::patch('/ads/{ad}/approve', [AdminAdController::class, 'approve'])
            ->middleware('permission:ads.approve')->name('ads.approve');

        Route::patch('/ads/{ad}/reject', [AdminAdController::class, 'reject'])
            ->middleware('permission:ads.reject')->name('ads.reject');

        Route::get('/reports', [AdminReportController::class, 'index'])
            ->middleware('permission:reports.view')->name('reports.index');

        Route::patch('/reports/{report}/dismiss', [AdminReportController::class, 'dismiss'])
            ->middleware('permission:reports.view')->name('reports.dismiss');

        Route::patch('/reports/{report}/takedown', [AdminReportController::class, 'takedown'])
            ->middleware('permission:ads.reject')->name('reports.takedown');

        Route::get('/', AdminDashboardController::class)->name('dashboard');
 });


