<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\KasirDashboardController;
use App\Http\Controllers\Dashboard\ChefDashboardController;
use Illuminate\Support\Facades\Auth;

/**
 * Thin dispatcher that hands off to the correct role-specific dashboard controller.
 *
 * WHY: a single stable URL (/dashboard) for everyone — users don't need to
 * remember different paths and login redirects stay simple. Per-role logic
 * stays in the dedicated controllers under Controllers/Dashboard.
 */
class DashboardController extends Controller
{
    /** Maps role name -> controller class. Single source of truth. */
    private const DISPATCH = [
        'Admin' => AdminDashboardController::class,
        'Kasir' => KasirDashboardController::class,
        'Chef'  => ChefDashboardController::class,
    ];

    public function index()
    {
        $role  = Auth::user()->role ?? 'Admin';
        $class = self::DISPATCH[$role] ?? AdminDashboardController::class;

        return app($class)();
    }
}
