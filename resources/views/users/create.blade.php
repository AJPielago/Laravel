@extends('layouts.app')

@section('content')
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
                        <div class="w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center ring-4 ring-white shadow-lg overflow-hidden">
                            <img id="photo-preview" class="w-full h-full object-cover hidden">
                            <svg id="default-avatar" class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                        <input type="file" name="photo" id="photo" accept="image/*"
                            onchange="previewImage(this)"
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
@endsection

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('photo-preview');
    const defaultAvatar = document.getElementById('default-avatar');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            defaultAvatar.classList.add('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
        defaultAvatar.classList.remove('hidden');
    }
}
</script>
@endpush
