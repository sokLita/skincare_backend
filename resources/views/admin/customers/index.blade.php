@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-200">
                <th class="px-4 py-2 text-left">ID</th>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Joined Date</th>
                <th class="px-4 py-2 text-left">Total Orders</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $customer->id }}</td>
                <td class="px-4 py-2">{{ $customer->name }}</td>
                <td class="px-4 py-2">{{ $customer->email }}</td>
                <td class="px-4 py-2">{{ $customer->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-2">{{ $customer->orders_count ?? 0 }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="text-blue-500 hover:text-blue-700 mr-2">View</a>
                    <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this customer? This will also delete all their orders.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-2 text-center text-gray-500">No customers found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4 px-4 py-2">
        {{ $customers->links() }}
    </div>
</div>
@endsection
