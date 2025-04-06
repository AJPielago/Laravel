@extends('layouts.app')

@section('content')
    @if(session('toast'))
        <x-toast 
            :message="session('toast')['message']"
            :type="session('toast')['type']"
            :details="session('toast')['details']"
        />
    @endif

    <div id="dynamic-toast"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-indigo-600">Users</h1>
                    <p class="mt-2 text-gray-600">Manage all registered users in the system.</p>
                </div>
                <a href="{{ route('users.create') }}" 
                   class="px-6 py-3 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition duration-300 font-semibold inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add User
                </a>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">ID</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Role</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b" data-user-id="{{ $user->id }}">
                                <td class="px-6 py-4">{{ $user->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        @if($user->photo)
                                            <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-600 font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="status-badge px-2 py-1 rounded-full text-sm font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="role-badge px-2 py-1 rounded-full text-sm font-medium {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                
                                        @if(Auth::id() !== $user->id)
                                            <button onclick="toggleUserStatus('{{ $user->id }}')" 
                                                    class="text-{{ $user->is_active ? 'red' : 'green' }}-600 hover:text-{{ $user->is_active ? 'red' : 'green' }}-900 font-medium">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button onclick="toggleUserRole('{{ $user->id }}')" 
                                                    class="text-purple-600 hover:text-purple-900 font-medium">
                                                Change Role
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function createToast(data) {
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    const icon = data.type === 'error' 
        ? '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        : '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    
    const bgColor = data.type === 'error' 
        ? 'bg-gradient-to-r from-red-500 to-rose-600'
        : 'bg-gradient-to-r from-indigo-500 to-indigo-600';

    toast.id = toastId;
    toast.className = 'fixed inset-0 flex items-center justify-center z-50 pointer-events-none';
    toast.innerHTML = `
        <div class="pointer-events-auto max-w-md w-full ${bgColor} text-white rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm bg-opacity-95 transform transition-all duration-300 scale-95 opacity-0">
            <div class="relative p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        ${icon}
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-lg font-semibold">${data.message}</p>
                        ${data.details ? `
                            <div class="mt-2 text-sm bg-white bg-opacity-20 rounded-lg p-3">
                                ${data.details.replace(/\n/g, '<br>')}
                            </div>
                        ` : ''}
                    </div>
                    <button onclick="closeToast('${toastId}')" class="flex-shrink-0 ml-4 p-1 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-white bg-opacity-30">
                    <div class="h-full bg-white animate-shrink origin-left"></div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('dynamic-toast').appendChild(toast);
    
    // Show toast
    requestAnimationFrame(() => {
        const toastContent = toast.querySelector('div');
        toastContent.classList.remove('scale-95', 'opacity-0');
        toastContent.classList.add('scale-100', 'opacity-100');
    });

    // Auto close after 5 seconds
    setTimeout(() => closeToast(toastId), 5000);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (!toast) return;

    const toastContent = toast.querySelector('div');
    toastContent.classList.remove('scale-100', 'opacity-100');
    toastContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        toast.remove();
    }, 300);
}

function toggleUserStatus(userId) {
    if (!confirm('Are you sure you want to change this user\'s status?')) return;

    $.ajax({
        url: `/users/${userId}/toggle-status`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const statusBadge = row.querySelector('.status-badge');
            const toggleButton = row.querySelector('button');

            if (response.is_active) {
                statusBadge.className = 'status-badge px-2 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                statusBadge.textContent = 'Active';
                toggleButton.textContent = 'Deactivate';
                toggleButton.className = 'text-red-600 hover:text-red-900 font-medium';
            } else {
                statusBadge.className = 'status-badge px-2 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
                statusBadge.textContent = 'Inactive';
                toggleButton.textContent = 'Activate';
                toggleButton.className = 'text-green-600 hover:text-green-900 font-medium';
            }

            if (response.toast) {
                createToast(response.toast);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'An error occurred while updating the user status.';
            createToast({
                type: 'error',
                message: 'Error',
                details: error
            });
        }
    });
}

function toggleUserRole(userId) {
    if (!confirm('Are you sure you want to change this user\'s role?')) return;

    $.ajax({
        url: `/users/${userId}/toggle-role`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const roleBadge = row.querySelector('.role-badge');

            if (response.is_admin) {
                roleBadge.className = 'role-badge px-2 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800';
                roleBadge.textContent = 'Admin';
            } else {
                roleBadge.className = 'role-badge px-2 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800';
                roleBadge.textContent = 'User';
            }

            if (response.toast) {
                createToast(response.toast);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'An error occurred while updating the user role.';
            createToast({
                type: 'error',
                message: 'Error',
                details: error
            });
        }
    });
}
</script>
@endpush