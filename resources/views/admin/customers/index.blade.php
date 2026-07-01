@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<style>
    .customers-table-wrap {
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .customers-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .customers-table thead th {
        background: #fafafa;
        color: #374151;
        font-weight: 600;
        letter-spacing: 0.03em;
        padding: 16px 20px;
        font-size: 0.875rem;
        border-bottom: 2px solid #e5e7eb;
        vertical-align: middle;
        white-space: nowrap;
    }

    .customers-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .customers-table tbody tr:not(:last-child) {
        border-bottom: 1px solid #f1f3f5;
    }

    .customers-table tbody tr:hover {
        background-color: #faf9fb;
    }

    .customers-table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border: none;
        font-size: 0.875rem;
        color: #334155;
    }

    .customers-table tbody td.text-right {
        text-align: right;
    }

    .btn-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.8125rem;
        font-weight: 500;
        background: #eff6ff;
        color: #3b82f6;
        text-decoration: none;
        transition: all 0.15s ease;
        border: none;
        cursor: pointer;
    }

    .btn-view:hover {
        background: #dbeafe;
        color: #2563eb;
    }

    .btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.8125rem;
        font-weight: 500;
        background: #fef2f2;
        color: #ef4444;
        text-decoration: none;
        transition: all 0.15s ease;
        border: none;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #fee2e2;
        color: #dc2626;
    }
</style>

<div class="customers-table-wrap">
    <table class="customers-table">
        <thead>
            <tr>
                <th>
                    <a href="{{ route('admin.customers.index', ['sort_by' => 'id', 'sort_dir' => request('sort_by') === 'id' && request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" style="text-decoration: none; color: inherit; cursor: pointer;">
                        ID
                        @if(request('sort_by') === 'id')
                            <span style="font-size: 0.75rem; margin-left: 2px;">{{ request('sort_dir') === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span style="font-size: 0.75rem; margin-left: 2px; opacity: 0.3;">↑</span>
                        @endif
                    </a>
                </th>
                <th>Name</th>
                <th>Email</th>
                <th>Joined Date</th>
                <th class="text-right">Total Orders</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                <td class="text-right">{{ $customer->orders_count ?? 0 }}</td>
                <td>
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn-view">
                        <i class="fas fa-eye" style="font-size: 0.75rem;"></i> View
                    </a>
                    <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this customer? This will also delete all their orders.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 16px 20px; color: #6b7280;">No customers found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4 px-4 py-2">
        {{ $customers->links() }}
    </div>
</div>
@endsection
