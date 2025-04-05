@props(['message', 'type' => 'success', 'details' => null])

@php
$toastId = 'toast-' . uniqid();
$bgColor = $type === 'error' 
    ? 'bg-gradient-to-r from-red-500 to-rose-600'
    : 'bg-gradient-to-r from-indigo-500 to-indigo-600';
$icon = $type === 'error' 
    ? '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
    : '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
@endphp

<div id="{{ $toastId }}" class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none">
    <div class="pointer-events-auto max-w-md w-full {{ $bgColor }} text-white rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm bg-opacity-95 transform transition-all duration-300 scale-95 opacity-0">
        <div class="relative p-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    {!! $icon !!}
                </div>
                <div class="flex-1 pt-1">
                    <p class="text-lg font-semibold">{{ $message }}</p>
                    @if($details)
                        <div class="mt-2 text-sm bg-white bg-opacity-20 rounded-lg p-3">
                            {!! nl2br(e($details)) !!}
                        </div>
                    @endif
                </div>
                <button onclick="closeToast('{{ $toastId }}')" class="flex-shrink-0 ml-4 p-1 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
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
</div>

<style>
@keyframes shrink {
    from {
        transform: scaleX(1);
    }
    to {
        transform: scaleX(0);
    }
}

.animate-shrink {
    animation: shrink 5s linear forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toastId = '{{ $toastId }}';
    const toast = document.getElementById(toastId);
    const toastContent = toast.querySelector('div');
    
    // Show toast
    requestAnimationFrame(() => {
        toastContent.classList.remove('scale-95', 'opacity-0');
        toastContent.classList.add('scale-100', 'opacity-100');
    });

    // Auto close after 5 seconds
    setTimeout(() => closeToast(toastId), 5000);
});

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    const toastContent = toast.querySelector('div');
    
    toastContent.classList.remove('scale-100', 'opacity-100');
    toastContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        toast.remove();
    }, 300);
}
</script>
