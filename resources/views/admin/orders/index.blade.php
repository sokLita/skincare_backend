@extends('admin.layout')

@section('title', 'Orders')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Orders Management</h2>
    <p class="text-gray-600 mt-1">View and manage all customer orders</p>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Order #</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Email</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Payment</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Shipping</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Total</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($orders as $order)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold text-gray-800">{{ $order->order_number ?? '#' . $order->id }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold text-gray-800">{{ $order->user->name ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-gray-600">{{ $order->user->email ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-gray-600">{{ $order->payment_method ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-gray-600">{{ $order->shipping_method ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-800">
                    ${{ number_format($order->total_amount, 2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="px-3 py-1 rounded-full text-xs font-bold
    {{ $order->status == 'delivered' ? 'bg-green-100 text-green-800' :
       ($order->status == 'shipped' ? 'bg-indigo-100 text-indigo-800' :
       ($order->status == 'processing' ? 'bg-blue-100 text-blue-800' :
       ($order->status == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'))) }}">
    {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-gray-600">{{ $order->created_at->format('M d, Y') }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-shopping-cart text-5xl mb-3 text-gray-300"></i>
                    <p class="text-lg">No orders found</p>
                    <p class="text-sm mt-1">Orders will appear here when customers make purchases</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($orders->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
