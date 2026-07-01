<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Get order status info for the chatbot.
     * Returns a templated message based on the order status.
     */
    public function orderStatus(Request $request, $orderId)
    {
        $user = $request->user();

        // Admins can look up any order; customers only their own
        if ($user && ($user->is_admin === true || $user->is_admin === 1 || $user->is_admin === '1')) {
            $order = Order::with('items.product')->findOrFail($orderId);
        } else {
            $order = Order::with('items.product')
                ->where('user_id', $user->id)
                ->findOrFail($orderId);
        }

        return response()->json([
            'success' => true,
            'data'    => $order->getChatbotMessage(),
        ]);
    }

    /**
     * Get all orders for the authenticated user, formatted for chatbot.
     */
    public function myOrders(Request $request)
    {
        $user = $request->user();
        $orders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($o) => $o->getChatbotMessage());

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * Handle free-text queries about an order.
     * Returns the most relevant order info based on the query.
     */
    public function query(Request $request)
    {
        $request->validate([
            'query'    => 'required|string|max:500',
            'order_id' => 'nullable',
        ]);

        $user = $request->user();
        $query = strtolower($request->input('query'));

        // If a specific order is requested (accepts numeric ID or order_number string)
        if ($request->filled('order_id')) {
            $orderRef = $request->input('order_id');
            $orderQuery = Order::with('items.product')->where('user_id', $user->id);
            if (is_numeric($orderRef)) {
                $orderQuery->where('id', $orderRef);
            } else {
                $orderQuery->where('order_number', $orderRef);
            }
            $order = $orderQuery->first();
            if (!$order) {
                return response()->json(['message' => 'Order not found.'], 404);
            }
            return $this->smartReply($query, $order);
        }

        // Find the most relevant order based on query context
        $orders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'message' => "You don't have any orders yet. Browse our products and place your first order!",
                ],
            ]);
        }

        // If query mentions "where" or "track", return the latest non-completed order
        if (str_contains($query, 'where') || str_contains($query, 'track') || str_contains($query, 'status')) {
            $activeOrder = $orders->first(fn($o) => !in_array($o->status, ['delivered', 'completed', 'cancelled']));
            if ($activeOrder) {
                return $this->smartReply($query, $activeOrder);
            }
        }

        // Default: return the most recent order
        return $this->smartReply($query, $orders->first());
    }

    /**
     * Confirm receipt of an order — marks Pending orders as Completed
     * when the customer confirms they received it.
     */
    public function confirmReceipt(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
        ]);

        $user = $request->user();
        $orderRef = $request->input('order_id');

        // Accept either a numeric ID or an order_number string (e.g. "ORD-00005")
        $order = $this->findOrderByRef($user->id, $orderRef);

        // Edge case: order not found
        if (!$order) {
            return response()->json([
                'success' => false,
                'data'    => [
                    'message' => "I couldn't find that order. Please check your order number and try again.",
                ],
            ]);
        }

        // Edge case: already completed
        if (in_array($order->status, ['completed', 'delivered'])) {
            return response()->json([
                'success' => false,
                'data'    => array_merge($order->getChatbotMessage(), [
                    'message' => "This order ({$order->getFormattedOrderNumber()}) is already marked as completed! Thank you for shopping with us. 🎉",
                ]),
            ]);
        }

        // Edge case: cancelled
        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'data'    => array_merge($order->getChatbotMessage(), [
                    'message' => "This order ({$order->getFormattedOrderNumber()}) was cancelled and can't be marked as received. If you have any questions, please contact support.",
                ]),
            ]);
        }

        // Edge case: not yet shipped / not pending
        if (!in_array($order->status, ['pending', 'processing', 'shipped'])) {
            return response()->json([
                'success' => false,
                'data'    => array_merge($order->getChatbotMessage(), [
                    'message' => "This order hasn't shipped yet — let us know if there's an issue.",
                ]),
            ]);
        }

        // --- Happy path: confirm receipt ---
        try {
            $oldStatus = $order->status;
            $order->update(['status' => 'completed']);

            $logged = $order->logStatusChange('completed', 'customer_confirmation');
            if (!$logged) {
                Log::warning("Order #{$order->id} confirmed but status_history not persisted (column may be missing)");
            }

            // Reload to get updated relationships
            $order->load('items.product');

            $productList = $order->items->map(fn($i) => "{$i->quantity}× {$i->product->name}")->implode(', ');
            $confirmMessage = "🎉 Thank you for confirming! We're so glad your order arrived safely. "
                . "Your order {$order->getFormattedOrderNumber()} ({$productList}) has been marked as Completed. "
                . "We hope you love it — thanks for shopping with us!";

            Log::info("Order #{$order->id} confirmed by customer via chatbot");

            return response()->json([
                'success' => true,
                'data'    => array_merge($order->getChatbotMessage(), [
                    'message' => $confirmMessage,
                    'status'  => 'completed',
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to confirm receipt for order #' . $order->id . ': ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'data'    => [
                    'message' => 'We received your confirmation but there was an issue updating your order — our team has been notified.',
                    'order_number' => $order->getFormattedOrderNumber(),
                ],
            ]);
        }
    }

    /**
     * Find an order by either numeric ID or order_number string.
     * Falls back to extracting the numeric portion from ORD-XXXXX format
     * if the order_number column is null.
     */
    private function findOrderByRef(int $userId, string $ref): ?Order
    {
        $query = Order::with('items.product')->where('user_id', $userId);

        if (is_numeric($ref)) {
            $query->where('id', $ref);
        } else {
            // Try by order_number first
            $query->where('order_number', $ref);
        }

        $order = $query->first();

        // If order not found and ref looks like ORD-XXXXX, try extracting the ID
        if (!$order && preg_match('/^ORD[-_]?(\d+)$/i', $ref, $matches)) {
            $numericId = (int) $matches[1];
            $order = Order::with('items.product')
                ->where('user_id', $userId)
                ->where('id', $numericId)
                ->first();
        }

        return $order;
    }

    /**
     * Build a smart reply based on the query context and order data.
     */
    private function smartReply(string $query, Order $order): \Illuminate\Http\JsonResponse
    {
        $basic = $order->getChatbotMessage();

        // Handle specific query types
        if (str_contains($query, 'what') && (str_contains($query, 'order') || str_contains($query, 'buy'))) {
            $items = $order->items->map(fn($i) => "{$i->quantity}x {$i->product->name} (\${$i->price})")->implode(', ');
            $message = "Your order {$basic['order_number']} contains: {$items}. Total: \$" . number_format($basic['total'], 2);
        } elseif (str_contains($query, 'where') || str_contains($query, 'track')) {
            $message = $basic['message'] . " Ordered on " . $order->created_at->format('M d, Y') . ".";
        } elseif (str_contains($query, 'total') || str_contains($query, 'cost') || str_contains($query, 'price') || str_contains($query, 'paid')) {
            $message = "Your order {$basic['order_number']} total is \$" . number_format($basic['total'], 2) . ". Payment method: {$basic['payment_method']}.";
        } elseif (str_contains($query, 'when') || str_contains($query, 'date') || str_contains($query, 'ship')) {
            $message = $basic['message'];
        } else {
            $message = $basic['message'];
        }

        return response()->json([
            'success' => true,
            'data'    => array_merge($basic, ['message' => $message]),
        ]);
    }
}
