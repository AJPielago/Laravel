<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Styles -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        @vite(['resources/css/app.css'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col">
            @include('layouts.header')

            <main class="flex-grow container mx-auto px-4 py-6">
                @yield('content')
            </main>

            @include('layouts.footer')
        </div>

        @if(session('success'))
            <div id="notification" class="fixed top-4 right-4 z-50 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg shadow-lg p-4 transform transition-all duration-500 opacity-0 translate-y-[-20px]">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const notification = document.getElementById('notification');
                    
                    // Show notification
                    setTimeout(() => {
                        notification.classList.remove('opacity-0', 'translate-y-[-20px]');
                        notification.classList.add('opacity-100', 'translate-y-0');
                    }, 100);
                    
                    // Hide notification after 5 seconds
                    setTimeout(() => {
                        notification.classList.remove('opacity-100', 'translate-y-0');
                        notification.classList.add('opacity-0', 'translate-y-[-20px]');
                        
                        // Remove from DOM after animation completes
                        setTimeout(() => {
                            notification.remove();
                        }, 500);
                    }, 5000);
                });
            </script>
        @endif

        @if(session('toast'))
            <div id="toast" 
                 class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-xl transform transition-all duration-500 ease-out translate-y-2 opacity-0">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <h3 class="font-medium">{{ session('toast.message') }}</h3>
                        @if(session('toast.details'))
                            <p class="text-sm opacity-90">{{ session('toast.details') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toast = document.getElementById('toast');
                    if (toast) {
                        // Show toast
                        setTimeout(() => {
                            toast.classList.remove('translate-y-2', 'opacity-0');
                        }, 100);

                        // Hide and remove toast
                        setTimeout(() => {
                            toast.classList.add('translate-y-2', 'opacity-0');
                            setTimeout(() => toast.remove(), 500);
                        }, 3000);
                    }
                });
            </script>
        @endif

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @vite(['resources/js/app.js'])
        @stack('scripts')
    </body>
</html>