<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Ecommerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Welcome Back</h1>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="email">Email</label>
                <input class="shadow-sm appearance-none border border-gray-200 rounded-lg w-full py-3 px-4 bg-blue-50 text-gray-700 leading-tight focus:outline-none focus:border-indigo-500 focus:bg-white"
                       id="email" type="email" name="email" value="rina@gmail.com" required autofocus>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="password">Password</label>
                <input class="shadow-sm appearance-none border border-gray-200 rounded-lg w-full py-3 px-4 bg-blue-50 text-gray-700 leading-tight focus:outline-none focus:border-indigo-500 focus:bg-white"
                       id="password" type="password" name="password" value="password" required>
            </div>
            <div class="mb-4">
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-200"
                        type="submit">Login</button>
            </div>
        </form>
        <div class="text-center mt-4">
            <span class="text-gray-500">No account?</span>
            <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium ml-1">Register here</a>
        </div>
    </div>
</body>
</html>
