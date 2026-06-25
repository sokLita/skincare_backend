<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()             { return view('admin.orders.index', ['orders' => Order::with('user')->latest()->paginate(20)]); }
    public function show(Order $order)  { return view('admin.orders.show', ['order' => $order->load('items.product', 'user')]); }
    public function updateStatus(Request $r, Order $order) {
        $r->validate(['status' => 'required|in:pending,processing,completed,cancelled']);
        $order->update(['status' => $r->status]);
        return back()->with('success', 'Order status updated.');
    }
}