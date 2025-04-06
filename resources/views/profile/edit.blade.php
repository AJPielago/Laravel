@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg p-4 sm:p-8 mx-auto max-w-3xl">
        <h1 class="text-2xl sm:text-3xl font-bold text-indigo-600 mb-6">Edit Profile</h1>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Information Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6 border border-gray-200">
            <h2 class="text-lg sm:text-xl font-semibold text-indigo-600 mb-4">Profile Information</h2>
            
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
                @csrf
                @method('PATCH')

                <!-- Profile Picture -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden border-2 border-gray-200 shadow-md">
                            <img 
                                src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('default-avatar.png') }}" 
                                alt="Profile Picture" 
                                class="w-full h-full object-cover"
                            >
                        </div>  
                        <div class="flex-1 w-full">
                            <input 
                                type="file" 
                                name="photo" 
                                id="photo" 
                                accept="image/*" 
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            >
                            @error('photo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150"
                    >
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Update Password Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6 border border-gray-200">
            <h2 class="text-lg sm:text-xl font-semibold text-indigo-600 mb-4">Update Password</h2>
            @include('profile.partials.update-password-form')
        </div>

        <!-- Delete Account Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
            <h2 class="text-lg sm:text-xl font-semibold text-red-600 mb-4">Delete Account</h2>
            <div class="text-sm text-gray-600 mb-4">
                Once your account is deleted, all of its resources and data will be permanently deleted. 
                Before deleting your account, please download any data or information that you wish to retain.
            </div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection