@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-indigo-600">Edit User Profile</h1>
                <p class="mt-2 text-gray-600">Update user information and settings</p>
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="flex items-center space-x-6 mb-6">
                    <div class="shrink-0">
                        <div class="w-24 h-24 rounded-full ring-4 ring-white shadow-lg overflow-hidden">
                            <img id="photo-preview" 
                                 class="w-full h-full object-cover" 
                                 src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('default-avatar.png') }}" 
                                 alt="{{ $user->name }}">
                        </div>
                    </div>
                    <div class="flex-1">
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Change Profile Photo</label>
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
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                    @error('name')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                    @error('email')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                    <p class="text-sm text-gray-500 mt-1">Leave blank to keep current password</p>
                    @error('password')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                </div>

                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-gray-700">Role</label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-2 rounded-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150"
                        @if($user->id === Auth::id()) disabled @endif>
                        <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @if($user->id === Auth::id())
                        <p class="text-sm text-gray-500 mt-1">You cannot change your own role</p>
                    @endif
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
                        Update User
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
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
