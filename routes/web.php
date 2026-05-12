<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| WHY this file is structured this way
|--------------------------------------------------------------------------
| Routes are grouped by *who can reach them*, not by feature. Reading top
| to bottom shows the access policy at a glance:
|   1. Public (no auth)
|   2. Authenticated (any role) — dashboard, logout, generic order browsing
|   3. Role-gated groups, ordered from broadest to narrowest
|
| The 'role:...' middleware (registered in bootstrap/app.php) accepts a
| comma-separated list of allowed roles, so we can colocate everyone who
| needs an endpoint without duplicating route definitions.
*/

// ---------- Public ----------
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/order/table/{tableId}',   [CustomerOrderController::class, 'show'])->name('customer.order');
Route::post('/order/table/place',      [CustomerOrderController::class, 'store'])->name('customer.order.store');
Route::get('/order/status/{id}',       [CustomerOrderController::class, 'status'])->name('customer.order.status');
Route::get('/order/status/{id}/state', [CustomerOrderController::class, 'statusState'])->name('customer.order.status.state');
Route::get('/reserve',                  [ReservationController::class, 'publicForm'])->name('reservation.public');
Route::post('/reserve',                 [ReservationController::class, 'publicStore'])->name('reservation.public.store');

// ---------- Authenticated (any role) ----------
Route::middleware(['auth'])->group(function () {
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // All staff can view their own receipt and look up orders.
    Route::get('/orders/{id}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
});

// ---------- POS (taking payment): Admin + Kasir ----------
// IMPORTANT: register the static /orders/create BEFORE the dynamic /orders/{order}
// below, otherwise the show route will swallow "create" as an ID.
Route::middleware(['auth', 'role:Admin,Kasir'])->group(function () {
    Route::get('/orders/create',  [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders',        [OrderController::class, 'store'])->name('orders.store');
});

// ---------- Sales floor: Admin, Kasir, Chef ----------
// Chef is included so the Kitchen Display can patch ticket status (Pending → Preparing → Done).
Route::middleware(['auth', 'role:Admin,Kasir,Chef'])->group(function () {
    Route::get('/orders',               [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}',       [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
});

// ---------- Kitchen: Admin + Chef ----------
Route::middleware(['auth', 'role:Admin,Chef'])->group(function () {
    Route::resource('inventory',  InventoryController::class);
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::resource('menus',      MenuController::class);
    Route::patch('/menus/{id}/toggle', [MenuController::class, 'toggleAvailability'])->name('menus.toggle');
});

// ---------- Floor management: Admin + Kasir ----------
// Kasir owns the host-stand workflow: tables and reservations.
Route::middleware(['auth', 'role:Admin,Kasir'])->group(function () {
    Route::resource('tables', TableController::class);
    Route::get('/tables/{id}/qr', [TableController::class, 'generateQR'])->name('tables.qr');
    Route::resource('reservations', ReservationController::class);
    Route::patch('/reservations/{id}/status', [ReservationController::class, 'updateStatus'])->name('reservations.update-status');
});

// ---------- Admin-only ----------
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::resource('suppliers',       SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::patch('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'markReceived'])->name('purchase-orders.receive');
    Route::resource('employees',       EmployeeController::class);
    Route::get('/reports',        [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});
