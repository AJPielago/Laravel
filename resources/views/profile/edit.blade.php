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
            
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 sm:space-y-6" id="profile-form">
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
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name
                            <span class="ml-1 text-xs text-indigo-600 opacity-0 transition-opacity duration-200" id="name-edit-indicator">editing...</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            onfocus="showEditIndicator('name')"
                            onblur="handleFieldUpdate(this, 'name')"
                        >
                        <span class="text-sm text-green-600 opacity-0 transition-opacity duration-300" id="name-success">Value updated!</span>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                            <span class="ml-1 text-xs text-indigo-600 opacity-0 transition-opacity duration-200" id="email-edit-indicator">editing...</span>
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            onfocus="showEditIndicator('email')"
                            onblur="handleFieldUpdate(this, 'email')"
                        >
                        <span class="text-sm text-green-600 opacity-0 transition-opacity duration-300" id="email-success">Value updated!</span>
                    </div>
                </div>

                <!-- Shipping Details -->
                <div class="space-y-4 sm:space-y-6">
                    <div>
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Shipping Address
                            <span class="ml-1 text-xs text-indigo-600 opacity-0 transition-opacity duration-200" id="shipping-edit-indicator">editing...</span>
                        </label>
                        <textarea
                            id="shipping_address"
                            name="shipping_address"
                            rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200"
                            onfocus="showEditIndicator('shipping')"
                            onblur="handleFieldUpdate(this, 'shipping')"
                        >{{ old('shipping_address', $user->shipping_address) }}</textarea>
                        <span class="text-sm text-green-600 opacity-0 transition-opacity duration-300" id="shipping-success">Value updated!</span>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                            <span class="ml-1 text-xs text-indigo-600 opacity-0 transition-opacity duration-200" id="phone-edit-indicator">editing...</span>
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            pattern="[0-9]*"
                            inputmode="numeric"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200"
                            onfocus="showEditIndicator('phone')"
                            onblur="handleFieldUpdate(this, 'phone')"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        >
                        <span class="text-sm text-green-600 opacity-0 transition-opacity duration-300" id="phone-success">Value updated!</span>
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

@push('scripts')
<script>
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const form = e.target;
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const result = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Profile updated successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: 'linear-gradient(to right, #6366f1, #a855f7)',
                color: '#ffffff',
                iconColor: '#ffffff',
                customClass: {
                    popup: 'rounded-lg shadow-xl'
                }
            });
        } else {
            throw new Error(result.message || 'Something went wrong');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to update profile',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: 'linear-gradient(to right, #ff6b6b, #ff4757)',
            color: '#ffffff',
            iconColor: '#ffffff',
            customClass: {
                popup: 'rounded-lg shadow-xl'
            }
        });
    }
});

function showEditIndicator(field) {
    document.getElementById(`${field}-edit-indicator`).classList.remove('opacity-0');
    document.getElementById(`${field}-edit-indicator`).classList.add('opacity-100');
}

function hideEditIndicator(field) {
    document.getElementById(`${field}-edit-indicator`).classList.remove('opacity-100');
    document.getElementById(`${field}-edit-indicator`).classList.add('opacity-0');
}

document.getElementById('phone').addEventListener('keypress', function(e) {
    if (!/[0-9]/.test(e.key)) {
        e.preventDefault();
    }
});

function handleFieldUpdate(input, field) {
    hideEditIndicator(field);
    if (input.value !== input.defaultValue) {
        const successElement = document.getElementById(`${field}-success`);
        successElement.classList.remove('opacity-0');
        successElement.classList.add('opacity-100');
        setTimeout(() => {
            successElement.classList.remove('opacity-100');
            successElement.classList.add('opacity-0');
        }, 2000);
        input.defaultValue = input.value;
    }
}
</script>
@endpush