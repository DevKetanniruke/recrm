<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryGridController;
use App\Http\Controllers\LeadConfigController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadFollowupController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

// Authenticated CRM Core & V0.2/V0.3/V0.4 Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    // V0.1 User Management & Profile
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    Route::middleware(['permission:users.create'])->group(function () {
        Route::get('/users-create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware(['permission:users.edit'])->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
    });

    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');

    // User Profile (Self)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Roles & Permission Matrix Management
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.update-permissions');

    // Companies & Settings Management
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

    // Activity Audit Log Explorer
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // V0.2 Project & Property Inventory Management
    Route::middleware(['permission:projects.view'])->group(function () {
        Route::resource('projects', ProjectController::class);
        Route::post('/buildings', [BuildingController::class, 'store'])->name('buildings.store');
    });

    Route::middleware(['permission:inventory.view'])->group(function () {
        // Configurable Unit Types
        Route::get('/unit-types', [UnitTypeController::class, 'index'])->name('unit-types.index');
        Route::post('/unit-types', [UnitTypeController::class, 'store'])->name('unit-types.store');

        // V0.2 Interactive Inventory Grid & Detailed Drawer
        Route::get('/inventory-grid', [InventoryGridController::class, 'index'])->name('inventory.grid');
        Route::post('/inventory/{unit}/status', [InventoryGridController::class, 'updateStatus'])->name('inventory.update-status');
        Route::post('/inventory/{unit}/pricing', [InventoryGridController::class, 'updatePricing'])->name('inventory.update-pricing');

        // Legacy/Matrix fallback routes
        Route::get('/inventory-matrix', [UnitController::class, 'matrix'])->name('units.matrix');
        Route::resource('units', UnitController::class)->only(['index', 'store']);
        Route::post('/units/{unit}/status', [UnitController::class, 'updateStatus'])->name('units.update-status');
    });

    // V0.3 Lead Management & Configuration Routes
    Route::middleware(['permission:leads.view'])->group(function () {
        // Lead Config (Admin Only)
        Route::get('/lead-config', [LeadConfigController::class, 'index'])->name('lead-config.index');
        Route::post('/lead-config/sources', [LeadConfigController::class, 'storeSource'])->name('lead-config.sources.store');
        Route::put('/lead-config/sources/{source}', [LeadConfigController::class, 'updateSource'])->name('lead-config.sources.update');
        Route::delete('/lead-config/sources/{source}', [LeadConfigController::class, 'destroySource'])->name('lead-config.sources.destroy');

        Route::post('/lead-config/statuses', [LeadConfigController::class, 'storeStatus'])->name('lead-config.statuses.store');
        Route::put('/lead-config/statuses/{status}', [LeadConfigController::class, 'updateStatus'])->name('lead-config.statuses.update');
        Route::delete('/lead-config/statuses/{status}', [LeadConfigController::class, 'destroyStatus'])->name('lead-config.statuses.destroy');

        // Follow-ups Quick Lists & Status Updates
        Route::get('/followups', [LeadFollowupController::class, 'index'])->name('followups.index');
        Route::post('/leads/{lead}/followups', [LeadFollowupController::class, 'store'])->name('followups.store');
        Route::post('/followups/{followup}/status', [LeadFollowupController::class, 'updateStatus'])->name('followups.update-status');

        // Duplicates & Import / Export
        Route::get('/leads/check-duplicates', [LeadController::class, 'checkDuplicatesApi'])->name('leads.check-duplicates');
        Route::get('/leads/duplicates', [LeadController::class, 'duplicatesView'])->name('leads.duplicates');
        Route::post('/leads/merge', [LeadController::class, 'mergeLeads'])->name('leads.merge');

        Route::get('/leads-import', [LeadController::class, 'importForm'])->name('leads.import.form');
        Route::post('/leads-import', [LeadController::class, 'processImport'])->name('leads.import.process');
        Route::get('/leads-import/failed-download', [LeadController::class, 'downloadFailedImportRows'])->name('leads.import.failed-download');

        Route::get('/leads-export', [LeadController::class, 'export'])->name('leads.export');

        // Core Lead Resource Routes
        Route::resource('leads', LeadController::class);
        Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity'])->name('leads.add-activity');
        Route::post('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
        
        // V0.4 Site Visit Logistics Routes
        Route::get('/site-visits', [SiteVisitController::class, 'index'])->name('site-visits.index');
        Route::post('/site-visits', [SiteVisitController::class, 'store'])->name('site-visits.store');
        Route::get('/site-visits/{siteVisit}', [SiteVisitController::class, 'show'])->name('site-visits.show');
        Route::post('/site-visits/{siteVisit}/dispatch', [SiteVisitController::class, 'dispatchCab'])->name('site-visits.dispatch');
        Route::post('/site-visits/{siteVisit}/check-in', [SiteVisitController::class, 'checkIn'])->name('site-visits.check-in');
        Route::post('/site-visits/{siteVisit}/check-out', [SiteVisitController::class, 'checkOut'])->name('site-visits.check-out');
        Route::post('/site-visits/{siteVisit}/status', [SiteVisitController::class, 'updateStatus'])->name('site-visits.update-status');

        // V0.4 Unified Sales Calendar
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'eventsApi'])->name('calendar.events');

        // V0.4 Offers & Multi-Round Negotiation Engine Routes
        Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
        Route::get('/offers-create', [OfferController::class, 'create'])->name('offers.create');
        Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
        Route::get('/offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
        Route::post('/offers/{offer}/counter', [OfferController::class, 'submitCounterOffer'])->name('offers.counter');
        Route::post('/offers/{offer}/approve', [OfferController::class, 'approve'])->name('offers.approve');
        Route::post('/offers/{offer}/reject', [OfferController::class, 'reject'])->name('offers.reject');
        Route::get('/offers/{offer}/pdf', [OfferController::class, 'downloadPdf'])->name('offers.pdf');
        Route::post('/offers/{offer}/convert-booking', [OfferController::class, 'convertToBooking'])->name('offers.convert-booking');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers/{customer}/kyc', [CustomerController::class, 'updateKyc'])->name('customers.update-kyc');
    });

    Route::middleware(['permission:bookings.view'])->group(function () {
        Route::resource('bookings', BookingController::class)->except(['destroy']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    });

    Route::middleware(['permission:payments.view'])->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });
});
