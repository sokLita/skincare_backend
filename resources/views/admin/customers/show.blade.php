@extends('admin.layout')

@section('title', 'Customer Details')

@section('content')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-gray-500 text-sm mb-1">Customer ID</h3>
            <p class="text-lg font-semibold">{{ $customer->id }}</p>
        </div>
        <div>
            <h3 class="text-gray-500 text-sm mb-1">Name</h3>
            <p class="text-lg font-semibold">{{ $customer->name }}</p>
        </div>
        <div>
            <h3 class="text-gray-500 text-sm mb-1">Email</h3>
            <p class="text-lg font-semibold">{{ $customer->email }}</p>
        </div>
        <div>
            <h3 class="text-gray-500 text-sm mb-1">Joined Date</h3>
            <p class="text-lg font-semibold">{{ $customer->created_at->format('M d, Y') }}</p>
        </div>
        <div>
            <h3 class="text-gray-500 text-sm mb-1">Total Orders</h3>
            <p class="text-lg font-semibold">{{ $customer->orders->count() }}</p>
        </div>
        <div>
            <h3 class="text-gray-500 text-sm mb-1">Total Spent</h3>
            <p class="text-lg font-semibold">${{ number_format($customer->orders->where('status', 'completed')->sum('total_amount'), 2) }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">Order History</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 text-left">Order ID</th>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Total</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Items</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->orders as $order)
                <tr class="border-b">
                    <td class="px-4 py-2">#{{ $order->id }}</td>
                    <td class="px-4 py-2">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-2">${{ number_format($order->total_amount, 2) }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $order->status == 'completed' ? 'bg-green-200 text-green-800' :
                               ($order->status == 'pending' ? 'bg-yellow-200 text-yellow-800' :
                               'bg-gray-200 text-gray-800') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $order->orderItems->count() }} items</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-2 text-center text-gray-500">No orders yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        <a href="{{ route('admin.customers.index') }}" class="text-blue-500 hover:text-blue-700">← Back to Customers</a>
    </div>
</div>
@endsection
