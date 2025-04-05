<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lara Shop - Create User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h1 class="text-2xl font-bold text-indigo-600">Lara Shop</h1>
            <nav class="space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-indigo-600">Products</a>
                <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-indigo-600">Orders</a>
                <a href="{{ route('users.index') }}" class="text-indigo-600">Users</a>
            </nav>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-3">
                    @if(Auth::user()->photo)
                        <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-indigo-600">
                    @else
                        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-semibold">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white shadow-lg rounded-lg p-8">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-indigo-600">Create New User</h1>
                        <p class="mt-2 text-gray-600">Add a new user to the system</p>
                    </div>

                    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div class="flex items-center space-x-6 mb-6">
                            <div class="shrink-0">
                                <div class="w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center ring-4 ring-white shadow-lg">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                                <input type="file" name="photo" id="photo" accept="image/*"
                                    class="w-full px-4 py-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition duration-150">
                                @error('photo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            @error('name')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            @error('email')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password" required
                                class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            @error('password')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                        </div>

                        <div class="space-y-2">
                            <label for="role" class="text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role" required
                                class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-4 mt-8">
                            <a href="{{ route('users.index') }}" 
                               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition duration-150">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition duration-150">
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white shadow-md py-4 mt-auto">
        <div class="container mx-auto px-6 text-center text-gray-600">
            <p>&copy; 2025 Lara Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
