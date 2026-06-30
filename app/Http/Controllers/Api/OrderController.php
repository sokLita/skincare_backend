<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, OrderItem};
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request) {
        try {
            $user = $request->user();
            if ($user && $user->is_admin) {
                $orders = Order::with('user', 'items.product')->latest()->paginate(20);
            } else {
                $orders = $user->orders()->with('items.product')->latest()->paginate(20);
            }
            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('OrderController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to load orders. Please try again later.'], 500);
        }
    }

    public function show(Request $request, $id) {
        try {
            $user = $request->user();
            if ($user && $user->is_admin) {
                $order = Order::with('items.product', 'user')->findOrFail($id);
            } else {
                $order = Order::where('user_id', $user->id)
                    ->with('items.product')->findOrFail($id);
            }
            return response()->json($order);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            Log::error('OrderController@show: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to load order details. Please try again later.'], 500);
        }
    }

    public function checkout(Request $request) {
        try {
            $request->validate([
                'shipping_address' => 'required|string',
                'payment_method'   => 'required|string',
                'shipping_method'  => 'required|string',
            ]);

            $user = $request->user();
            $cartItems = $user->cart()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Your cart is empty.'], 400);
            }

            $total = $cartItems->sum(fn($i) => $i->product->price * $i->quantity);
            $orderNumber = 'ORD-' . str_pad(Order::max('id') + 1, 5, '0', STR_PAD_LEFT);

            $order = Order::create([
                'user_id'          => $user->id,
                'total_amount'     => $total,
                'shipping_address' => $request->shipping_address,
                'billing_address'  => $request->billing_address ?? $request->shipping_address,
                'payment_method'   => $request->payment_method,
                'shipping_method'  => $request->shipping_method,
                'notes'            => $request->notes ?? '',
                'order_number'     => $orderNumber,
                'status'           => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);
                $item->product->decrement('stock', $item->quantity);
            }

            $user->cart()->delete();
            $order->load('items.product');

            return response()->json([
                'message' => 'Order placed successfully! Thank you for your purchase.',
                'order'   => $order,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Please check your order details.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('OrderController@checkout: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Unable to place your order. Please try again later.',
            ], 500);
        }
    }

    public function cancel(Request $request, $id) {
        try {
            $user = $request->user();
            $order = Order::where('user_id', $user->id)->findOrFail($id);

            if (!in_array($order->status, ['pending', 'processing'])) {
                return response()->json([
                    'message' => 'This order cannot be cancelled. Only pending or processing orders can be cancelled.',
                ], 400);
            }

            $order->update(['status' => 'cancelled']);
            return response()->json([
                'message' => 'Order cancelled successfully.',
                'order'   => $order->fresh()->load('items.product'),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            Log::error('OrderController@cancel: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to cancel order. Please try again later.'], 500);
        }
    }
}