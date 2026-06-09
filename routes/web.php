<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

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
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/order/table/{tableId}', [CustomerOrderController::class, 'show'])->name('customer.order');
Route::post('/order/table/place', [CustomerOrderController::class, 'store'])->name('customer.order.store');
Route::get('/order/status/{id}', [CustomerOrderController::class, 'status'])->name('customer.order.status');
Route::get('/order/status/{id}/state', [CustomerOrderController::class, 'statusState'])->name('customer.order.status.state');
Route::get('/reserve', [ReservationController::class, 'publicForm'])->name('reservation.public');
Route::post('/reserve', [ReservationController::class, 'publicStore'])->name('reservation.public.store');
Route::get('/reserve/availability', [ReservationController::class, 'availability'])->name('reservation.availability');

// ---------- Authenticated (any role) ----------
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // All staff can view their own receipt and look up orders.
    Route::get('/orders/{id}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
});

// ---------- POS (taking payment): Admin + Kasir ----------
// IMPORTANT: register the static /orders/create BEFORE the dynamic /orders/{order}
// below, otherwise the show route will swallow "create" as an ID.
Route::middleware(['auth', 'role:Admin,Kasir'])->group(function () {
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::patch('/orders/{id}/collect-payment', [OrderController::class, 'collectPayment'])->name('orders.collect-payment');
});

// ---------- Sales floor: Admin, Kasir, Chef ----------
// Chef is included so the Kitchen Display can patch ticket status (Pending → Preparing → Done).
Route::middleware(['auth', 'role:Admin,Kasir,Chef'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
});

// ---------- Kitchen: Admin + Chef ----------
Route::middleware(['auth', 'role:Admin,Chef'])->group(function () {
    // CRUD here is modal-based on the index pages — there are no standalone
    // create/edit/show screens, so exclude those actions to keep the route
    // table honest (a registered route with no controller method 500s).
    Route::resource('inventory', InventoryController::class)->except(['create', 'show', 'edit', 'update']);
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::resource('menus', MenuController::class)->except(['create', 'show', 'edit']);
    Route::get('/menus/{id}/recipe', [MenuController::class, 'recipe'])->name('menus.recipe');
    Route::put('/menus/{id}/recipe', [MenuController::class, 'updateRecipe'])->name('menus.recipe.update');
});

// ---------- Floor management: Admin + Kasir ----------
// Kasir owns the host-stand workflow: tables and reservations.
Route::middleware(['auth', 'role:Admin,Kasir'])->group(function () {
    Route::get('/tables/qr-sheet', [TableController::class, 'qrSheet'])->name('tables.qr-sheet');
    Route::resource('tables', TableController::class)->except(['create', 'show', 'edit']);
    Route::get('/tables/{id}/qr', [TableController::class, 'generateQR'])->name('tables.qr');
    Route::resource('reservations', ReservationController::class)->except(['create', 'show', 'edit']);
    Route::patch('/reservations/{id}/status', [ReservationController::class, 'updateStatus'])->name('reservations.update-status');
});

// ---------- Admin-only ----------
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['create', 'edit', 'update']);
    Route::patch('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'markReceived'])->name('purchase-orders.receive');
    Route::resource('employees', EmployeeController::class)->except(['create', 'show', 'edit']);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});
