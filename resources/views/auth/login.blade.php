@extends('layouts.app')

@section('hideHeaderAuth', true)
@section('hideHeaderLogin', true)

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-8rem)]">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">Login to Your Account</h2>

        @if($errors->any())
            <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-red-600">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block font-medium text-gray-700">Email</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('email') }}">
            </div>

            <div>
                <label class="block font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="showPassword" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-gray-700">Show Password</span>
                    </label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-indigo-600 font-semibold hover:underline">Forgot Password?</a>
                @endif
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white px-6 py-3 rounded-full font-semibold shadow-lg hover:bg-indigo-700 transition duration-300">
                Login
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">Don't have an account? 
            <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:underline">Register</a>
        </p>
    </div>
</div>

<script>
    document.getElementById('showPassword').addEventListener('change', function() {
        var passwordInput = document.getElementById('password');
        passwordInput.type = this.checked ? 'text' : 'password';
    });
</script>
@endsection
