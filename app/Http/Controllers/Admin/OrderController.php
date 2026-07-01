<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request) {
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('sort_dir', 'asc');
        $filter = $request->query('filter');

        // Validate to prevent SQL injection
        $sortBy = in_array($sortBy, ['id']) ? $sortBy : 'id';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $query = Order::with('user');

        // Filter by status
        if ($filter === 'completed') {
            $query->whereIn('status', ['completed', 'delivered']);
        } elseif ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'processing') {
            $query->where('status', 'processing');
        } elseif ($filter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        return view('admin.orders.index', [
            'orders' => $query
                ->orderBy($sortBy, $sortDir)
                ->paginate(20)
                ->withQueryString(),
            'currentFilter' => $filter,
        ]);
    }

    public function show(Order $order)  { return view('admin.orders.show', ['order' => $order->load('items.product', 'user')]); }

    public function updateStatus(Request $r, Order $order) {
        $r->validate(['status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled']);

        $oldStatus = $order->status;
        $newStatus = $r->status;

        $order->update(['status' => $newStatus]);
        $order->logStatusChange($newStatus, "Updated from {$oldStatus} by admin");

        Log::info("Order #{$order->id} status changed: {$oldStatus} → {$newStatus}");

        $message = "Order {$order->getFormattedOrderNumber()} marked as {$newStatus}";
        if ($newStatus === 'completed' || $newStatus === 'delivered') {
            $message .= " — customer will see this in their order chatbot";
        }

        return back()->with('success', $message);
    }

    /**
     * API endpoint for inline status update (used by the admin order table).
     */
    public function updateStatusApi(Request $r, Order $order) {
        $r->validate(['status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled']);

        $oldStatus = $order->status;
        $newStatus = $r->status;

        $order->update(['status' => $newStatus]);
        $order->logStatusChange($newStatus, "Updated from {$oldStatus} by admin (inline)");

        Log::info("Order #{$order->id} status changed: {$oldStatus} → {$newStatus} (inline)");

        $message = "Order {$order->getFormattedOrderNumber()} marked as {$newStatus}";
        if ($newStatus === 'completed' || $newStatus === 'delivered') {
            $message .= " — customer will see this in their order chatbot";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'order'   => $order->fresh()->load('user'),
        ]);
    }
}