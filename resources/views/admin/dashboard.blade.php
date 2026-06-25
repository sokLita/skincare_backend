@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Products</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalProducts }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-4">
                <i class="fas fa-box text-blue-500 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Orders</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalOrders }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-4">
                <i class="fas fa-shopping-bag text-green-500 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Customers</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalUsers }}</p>
            </div>
            <div class="bg-purple-100 rounded-full p-4">
                <i class="fas fa-users text-purple-500 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-800">${{ number_format($totalRevenue, 2) }}</p>
            </div>
            <div class="bg-yellow-100 rounded-full p-4">
                <i class="fas fa-dollar-sign text-yellow-500 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            <i class="fas fa-clock mr-2"></i>Recent Orders
        </h2>
        <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
            View All <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-semibold text-gray-800">#{{ $order->id }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold mr-3">
                                {{ substr($order->user->name ?? 'N', 0, 1) }}
                            </div>
                            <span class="text-gray-700">{{ $order->user->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-800">
                        ${{ number_format($order->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            {{ $order->status == 'completed' ? 'bg-green-100 text-green-800' :
                               ($order->status == 'pending' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                        {{ $order->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>No orders yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
