<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model {
    protected $fillable = [
        'user_id', 'total_amount', 'status', 'status_history',
        'shipping_address', 'billing_address', 'payment_method',
        'shipping_method', 'notes', 'order_number'
    ];

    protected $casts = [
        'status_history' => 'array',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }

    /**
     * Log a status change with current timestamp.
     * Returns true on success, false on failure (with server-side logging).
     */
    public function logStatusChange(string $newStatus, ?string $note = null): bool
    {
        try {
            $history = $this->status_history ?? [];
            $history[] = [
                'status'     => $newStatus,
                'timestamp'  => now()->toIso8601String(),
                'note'       => $note,
            ];
            $this->status_history = $history;
            $this->save();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log status change for order #' . $this->id . ': ' . $e->getMessage(), [
                'order_id' => $this->id,
                'status'   => $newStatus,
                'trace'    => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get the formatted order number.
     */
    public function getFormattedOrderNumber(): string
    {
        return $this->order_number ?? 'ORD-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get product names as a comma-separated string.
     */
    public function getProductNames(): string
    {
        if (!$this->relationLoaded('items')) {
            return '';
        }
        return $this->items->pluck('product.name')->implode(', ');
    }

    /**
     * Get a chatbot-friendly status message for this order.
     */
    public function getChatbotMessage(): array
    {
        $orderNumber = $this->getFormattedOrderNumber();
        $productNames = $this->relationLoaded('items') ? $this->getProductNames() : '';

        $message = match ($this->status) {
            'pending'    => "Your order {$orderNumber} is currently pending. Payment received via {$this->payment_method} — we'll notify you once it ships.",
            'processing' => "Your order {$orderNumber} is being processed. We're preparing your items and will ship them soon!",
            'shipped'    => "Your order {$orderNumber} has been shipped! You should receive it within a few days.",
            'delivered', 'completed' => "Your order {$orderNumber} has been marked as completed. You received: {$productNames}. Thank you for your purchase!",
            'cancelled'  => "Your order {$orderNumber} has been cancelled. If you have any questions, please contact support.",
            default      => "Your order {$orderNumber} is currently {$this->status}.",
        };

        $items = $this->relationLoaded('items') ? $this->items->map(fn($i) => [
            'name'     => $i->product->name ?? 'Product',
            'quantity' => $i->quantity,
            'price'    => (float) $i->price,
        ]) : [];

        return [
            'order_number' => $orderNumber,
            'status'       => $this->status,
            'message'      => $message,
            'items'        => $items,
            'total'        => (float) $this->total_amount,
            'payment_method' => $this->payment_method,
            'shipping_method' => $this->shipping_method,
            'date'         => $this->created_at->toIso8601String(),
            'status_history' => $this->status_history ?? [],
        ];
    }
}