<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Order, User};

class DashboardController extends Controller
{
    public function index() {
        return view('admin.dashboard', [
            'totalProducts'  => Product::count(),
            'totalOrders'    => Order::count(),
            'totalUsers'     => User::where('is_admin', false)->count(),
            'totalRevenue'   => Order::where('status', 'completed')->sum('total_amount'),
            'recentOrders'   => Order::with('user')->latest()->take(5)->get(),
        ]);
    }
}