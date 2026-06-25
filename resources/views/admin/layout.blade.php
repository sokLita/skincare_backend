<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Ecommerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar-link { transition: all 0.3s ease; }
        .sidebar-link:hover { transform: translateX(5px); }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-indigo-600 to-purple-700 text-white shadow-xl">
            <div class="p-6 border-b border-indigo-500">
                <h2 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Admin Panel
                </h2>
            </div>
            <nav class="mt-6 px-3">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center py-3 px-4 rounded-lg mb-2 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <i class="fas fa-home w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="sidebar-link flex items-center py-3 px-4 rounded-lg mb-2 {{ request()->routeIs('admin.categories.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <i class="fas fa-folder w-6"></i>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="sidebar-link flex items-center py-3 px-4 rounded-lg mb-2 {{ request()->routeIs('admin.products.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <i class="fas fa-box w-6"></i>
                    <span>Products</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="sidebar-link flex items-center py-3 px-4 rounded-lg mb-2 {{ request()->routeIs('admin.orders.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <i class="fas fa-shopping-bag w-6"></i>
                    <span>Orders</span>
                </a>
                <a href="{{ route('admin.customers.index') }}" class="sidebar-link flex items-center py-3 px-4 rounded-lg mb-2 {{ request()->routeIs('admin.customers.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <i class="fas fa-users w-6"></i>
                    <span>Customers</span>
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="sidebar-link flex items-center py-3 px-4 rounded-lg mb-2 {{ request()->routeIs('admin.reviews.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <i class="fas fa-star w-6"></i>
                    <span>Reviews</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-8 py-4">
                    <h1 class="text-2xl font-bold text-gray-800">@yield('title')</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">
                            <i class="fas fa-user-circle mr-2"></i>
                            {{ Auth::user()->name }}
                        </span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 shadow">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
