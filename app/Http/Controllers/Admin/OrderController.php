<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request) {
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('sort_dir', 'asc');

        // Validate to prevent SQL injection
        $sortBy = in_array($sortBy, ['id']) ? $sortBy : 'id';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        return view('admin.orders.index', [
            'orders' => Order::with('user')
                ->orderBy($sortBy, $sortDir)
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function show(Order $order)  { return view('admin.orders.show', ['order' => $order->load('items.product', 'user')]); }
    public function updateStatus(Request $r, Order $order) {
        $r->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);
        $order->update(['status' => $r->status]);
        return back()->with('success', 'Order status updated.');
    }
}