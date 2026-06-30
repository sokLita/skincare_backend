<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(Request $request) {
        $totalProducts = Product::count();
        $totalOrders   = Order::count();
        $totalUsers    = User::where('is_admin', false)->count();
        $totalRevenue  = Order::whereIn('status', ['pending', 'processing', 'shipped', 'delivered'])->sum('total_amount');
        $recentOrders  = Order::with('user')->latest()->take(5)->get()->map(function ($order) {
            return [
                'id'           => $order->id,
                'order_number' => $order->order_number ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'user'         => $order->user ? ['name' => $order->user->name] : null,
                'total_amount' => (float) $order->total_amount,
                'status'       => $order->status,
                'created_at'   => $order->created_at,
            ];
        });

        return response()->json([
            'totalProducts' => $totalProducts,
            'totalOrders'   => $totalOrders,
            'totalUsers'    => $totalUsers,
            'totalRevenue'  => $totalRevenue,
            'recentOrders'  => $recentOrders,
            'newOrdersCount' => Order::where('status', 'pending')->count(),
        ]);
    }

    public function newOrdersCount(Request $request) {
        $count = Order::where('status', 'pending')->count();
        return response()->json(['count' => $count]);
    }

    public function orders(Request $request) {
        $orders = Order::with('user')->latest()->paginate(20);
        return response()->json($orders);
    }

    public function orderDetail(Request $request, $id) {
        $order = Order::with('items.product', 'user')->findOrFail($id);
        return response()->json($order);
    }
}