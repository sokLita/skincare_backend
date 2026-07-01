@extends('admin.layout')

@section('title', 'Orders')

@section('content')
<style>
    /* ─── Card ─── */
    .orders-card {
        font-family: 'Inter', sans-serif;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    /* ─── Header / Toolbar ─── */
    .orders-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 24px 28px 0;
    }

    .orders-toolbar .page-title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: #0f172a;
        margin: 0;
    }

    .orders-toolbar .page-subtitle {
        font-size: 0.85rem;
        color: #94a3b8;
        margin: 2px 0 0;
    }

    .orders-search {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .orders-search .search-wrapper {
        position: relative;
    }

    .orders-search .search-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .orders-search .search-wrapper input {
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        padding: 9px 14px 9px 38px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        color: #0f172a;
        width: 240px;
        outline: none;
        transition: all 0.2s ease;
    }

    .orders-search .search-wrapper input:focus {
        border-color: #818cf8;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
    }

    .orders-search .search-wrapper input::placeholder {
        color: #94a3b8;
    }

    .orders-search .filter-btn {
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 500;
        padding: 9px 18px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .orders-search .filter-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* ─── Table ─── */
    .orders-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }

    .orders-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 18px 22px;
        border-bottom: none;
        vertical-align: middle;
        white-space: nowrap;
    }

    .orders-table thead th.text-right {
        text-align: right;
    }

    .orders-table thead th.text-center {
        text-align: center;
    }

    .orders-table tbody tr {
        transition: background-color 0.15s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .orders-table tbody tr:last-child {
        border-bottom: none;
    }

    .orders-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .orders-table tbody td {
        padding: 18px 22px;
        vertical-align: middle;
        color: #334155;
        font-size: 0.85rem;
        border: none;
        line-height: 1.5;
    }

    .orders-table tbody td.text-right {
        text-align: right;
    }

    .orders-table tbody td.text-center {
        text-align: center;
    }

    /* ─── Order # ─── */
    .cell-order-number {
        font-family: 'Inter', monospace;
        font-weight: 600;
        color: #0f172a;
        font-size: 0.8rem;
        letter-spacing: -0.01em;
    }

    /* ─── Customer Avatar + Name/Email ─── */
    .customer-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .customer-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
        letter-spacing: 0.02em;
    }

    .customer-info {
        display: flex;
        flex-direction: column;
    }

    .customer-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.85rem;
        line-height: 1.3;
    }

    .customer-email {
        font-size: 0.78rem;
        color: #94a3b8;
        line-height: 1.3;
    }

    /* ─── Payment / Shipping ─── */
    .cell-method {
        color: #475569;
        font-size: 0.8rem;
    }

    /* ─── Total ─── */
    .cell-total {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.88rem;
        font-family: 'Inter', monospace;
        font-variant-numeric: tabular-nums;
    }

    /* ─── Status Badges with Dot ─── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-badge .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-pending {
        background: #fffbeb;
        color: #b45309;
    }
    .status-pending .dot { background: #f59e0b; }

    .status-processing {
        background: #eff6ff;
        color: #2563eb;
    }
    .status-processing .dot { background: #3b82f6; }

    .status-shipped {
        background: #eef2ff;
        color: #4f46e5;
    }
    .status-shipped .dot { background: #6366f1; }

    .status-delivered {
        background: #f0fdf4;
        color: #16a34a;
    }
    .status-delivered .dot { background: #22c55e; }

    .status-cancelled {
        background: #fef2f2;
        color: #dc2626;
    }
    .status-cancelled .dot { background: #ef4444; }

    /* ─── Date ─── */
    .cell-date {
        color: #64748b;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    /* ─── Action Button ─── */
    .btn-action-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50px;
        border: none;
        background: #eff6ff;
        color: #3b82f6;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        position: relative;
    }

    .btn-action-view:hover {
        background: #dbeafe;
        color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-action-view[data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) scale(0.9);
        background: #0f172a;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: all 0.15s ease;
        font-family: 'Inter', sans-serif;
    }

    .btn-action-view[data-tooltip]:hover::after {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }

    /* ─── Pagination ─── */
    .orders-pagination {
        padding: 16px 28px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .orders-pagination .pagination-info {
        color: #94a3b8;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* ─── Empty State ─── */
    .empty-state {
        padding: 64px 28px;
        text-align: center;
    }

    .empty-state i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 14px;
    }

    .empty-state p {
        color: #94a3b8;
        font-size: 0.9rem;
        margin: 0;
    }

    .empty-state p:first-of-type {
        font-weight: 600;
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 4px;
    }

    /* ─── Filter Tabs ─── */
    .filter-tab:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }

    /* ─── Actions cell with flex layout ─── */
    .actions-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 54px;
    }

    .actions-cell .btn-action-view {
        flex-shrink: 0;
    }

    /* ─── Responsive Mobile ─── */
    @media (max-width: 767.98px) {
        .orders-toolbar {
            padding: 20px 18px 0;
            flex-direction: column;
            align-items: stretch;
        }

        .orders-search .search-wrapper input {
            width: 100%;
        }

        .orders-search {
            flex-direction: column;
        }

        .orders-table tbody td {
            padding: 12px 14px;
            font-size: 0.8rem;
        }

        .orders-pagination {
            flex-direction: column;
            text-align: center;
            padding: 16px 18px;
        }
    }

    /* ─── Tablet fine-tune ─── */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .orders-table thead th,
        .orders-table tbody td {
            padding: 12px 14px;
            font-size: 0.78rem;
        }
    }
</style>

<div class="orders-card">
    <!-- Toolbar -->
    <div class="orders-toolbar">
        <div>
            <h2 class="page-title">                            <i class="fas fa-shopping-cart mr-2" style="color: #b8456a;"></i>Orders
            </h2>
            <p class="page-subtitle">View and manage all customer orders</p>

            <!-- Filter Tabs -->
            <div class="filter-tabs" style="margin-top: 14px; margin-bottom: 24px; display: flex; gap: 14px; flex-wrap: wrap;">
                <a href="{{ route('admin.orders.index') }}"
                   class="filter-tab {{ !$currentFilter ? 'active' : '' }}"
                   style="padding: 8px 20px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;
                          {{ !$currentFilter ? 'background: #b8456a; color: #fff;' : 'background: #fdf2f6; color: #7a1f3d;' }}">
                    All Orders
                </a>
                <a href="{{ route('admin.orders.index', ['filter' => 'pending']) }}"
                   class="filter-tab {{ $currentFilter === 'pending' ? 'active' : '' }}"
                   style="padding: 8px 20px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;
                          {{ $currentFilter === 'pending' ? 'background: #b8456a; color: #fff;' : 'background: #fdf2f6; color: #7a1f3d;' }}">
                    <span>⏳</span> Pending
                </a>
                <a href="{{ route('admin.orders.index', ['filter' => 'completed']) }}"
                   class="filter-tab {{ $currentFilter === 'completed' ? 'active' : '' }}"
                   style="padding: 8px 20px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;
                          {{ $currentFilter === 'completed' ? 'background: #b8456a; color: #fff;' : 'background: #fdf2f6; color: #7a1f3d;' }}">
                    <span>✅</span> Completed
                </a>
            </div>
        </div>
        <div class="orders-search">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search orders…" id="orderSearch" aria-label="Search orders" oninput="filterOrders(this.value)">
            </div>
            <button class="filter-btn" onclick="toggleFilters()">
                <i class="fas fa-sliders-h"></i> Filters
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="orders-table-wrap">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>
                        <a href="{{ route('admin.orders.index', ['sort_by' => 'id', 'sort_dir' => request('sort_by') === 'id' && request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" style="text-decoration: none; color: inherit; cursor: pointer;">
                            Order #
                            @if(request('sort_by') === 'id')
                                <span style="font-size: 0.75rem; margin-left: 2px;">{{ request('sort_dir') === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span style="font-size: 0.75rem; margin-left: 2px; opacity: 0.3;">↑</span>
                            @endif
                        </a>
                    </th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th class="text-center">Shipping</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Status</th>
                    <th>Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $initial = strtoupper(substr($order->user->name ?? '?', 0, 1));
                    $colors = ['#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e', '#14b8a6', '#06b6d4', '#3b82f6'];
                    $colorIndex = crc32(optional($order->user)->email ?? $order->id) % count($colors);
                    $avatarColor = $colors[abs($colorIndex)];
                @endphp
                <tr class="order-row">
                    <td>
                        <span class="cell-order-number">{{ $order->order_number ?? '#' . $order->id }}</span>
                    </td>
                    <td>
                        <div class="customer-cell">
                            <div class="customer-avatar" style="background: {{ $avatarColor }};">
                                {{ $initial }}
                            </div>
                            <div class="customer-info">
                                <span class="customer-name">{{ $order->user->name ?? 'N/A' }}</span>
                                <span class="customer-email">{{ $order->user->email ?? '' }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="cell-method">{{ $order->payment_method ?? 'N/A' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="cell-method">{{ $order->shipping_method ?? 'N/A' }}</span>
                    </td>
                    <td class="text-right">
                        <span class="cell-total">${{ number_format($order->total_amount, 2) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $order->status }}">
                            <span class="dot"></span>
                            {{ ucfirst($order->status) }}
                        </span>
                        @if($order->status_history && collect($order->status_history)->contains('note', 'customer_confirmation'))
                            <span class="customer-confirmed-badge" style="display: block; margin-top: 4px; font-size: 0.65rem; color: #16a34a; font-weight: 600; white-space: nowrap;">
                                ✅ Confirmed by customer
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="cell-date">
                            <i class="far fa-calendar-alt mr-1" style="color: #cbd5e1; font-size: 0.7rem;"></i>
                            {{ $order->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td class="text-center actions-cell">
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="btn-action-view"
                           data-tooltip="View Order"
                           title="View Order"
                           aria-label="View Order {{ $order->order_number ?? $order->id }}">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <p>No orders found</p>
                            <p>Orders will appear here when customers make purchases</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
    <div class="orders-pagination">
        <span class="pagination-info">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </span>
        <div>
            {{ $orders->links() }}
        </div>
    </div>
    @else
    <div class="orders-pagination">
        <span class="pagination-info">
            Showing all {{ $orders->total() }} {{ Str::plural('order', $orders->total()) }}
        </span>
    </div>
    @endif
</div>

<script>
    function filterOrders(query) {
        const rows = document.querySelectorAll('.order-row');
        const q = query.toLowerCase().trim();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = !q || text.includes(q) ? '' : 'none';
        });
    }

    function toggleFilters() {
        // Placeholder for future filter panel
        // Could expand into a dropdown or slide-out panel
    }

    // Add toast animation keyframes (kept for future use)
    const styleSheet = document.createElement('style');
    styleSheet.textContent = `@keyframes toastIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }`;
    document.head.appendChild(styleSheet);
</script>
@endsection
