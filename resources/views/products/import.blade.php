<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lara Shop - Import Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    @include('layouts.header')

    @if(session('toast'))
        <x-toast 
            :message="session('toast')['message']"
            :type="session('toast')['type']"
            :details="session('toast')['details']"
        />
    @endif

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            @auth
                @if(auth()->user()->role === 'admin')
                    <div class="bg-white shadow-lg rounded-lg p-8 max-w-4xl mx-auto">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                            <h1 class="text-2xl sm:text-3xl font-bold text-indigo-600">Import Products</h1>
                            <a href="{{ route('products.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                                ← Back to Products
                            </a>
                        </div>

                        {{-- Status Messages --}}
                        <div class="space-y-4 mb-6">
                            @if(session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                    <span class="block sm:inline">{{ session('success') }}</span>
                                </div>
                            @endif
                            
                            @if(session('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <span class="block sm:inline">{{ session('error') }}</span>
                                </div>
                            @endif
                            
                            @if($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    
                        <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <div class="mb-6">
                                    <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                                        Excel File (XLSX, XLS, CSV) - Max 5MB
                                    </label>
                                    <input type="file"
                                           name="excel_file"
                                           id="excel_file"
                                           required
                                           accept=".xlsx,.xls,.csv"
                                           class="block w-full text-sm text-gray-500
                                                  file:mr-4 file:py-2 file:px-4
                                                  file:rounded-lg file:border-0
                                                  file:text-sm file:font-semibold
                                                  file:bg-indigo-50 file:text-indigo-700
                                                  hover:file:bg-indigo-100 transition-colors duration-200">
                                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 5MB</p>
                                </div>
                                
                                <div class="mt-6">
                                    <button type="submit"
                                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                        Import Products
                                    </button>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Excel File Format Requirements</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="font-medium text-gray-800 mb-2">Required Columns:</h4>
                                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                            <li><span class="font-medium">name</span> (string)</li>
                                            <li><span class="font-medium">price</span> (numeric, min: 0)</li>
                                            <li><span class="font-medium">stock</span> (integer, min: 0)</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800 mb-2">Optional Columns:</h4>
                                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                            <li><span class="font-medium">description</span> (text)</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ asset('sample-import-template.xlsx') }}" 
                                       class="inline-flex items-center text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors duration-200">
                                        Download Sample Template
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Access Denied</strong>
                            <span class="block sm:inline">You don't have permission to import products.</span>
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Authentication Required</strong>
                        <span class="block sm:inline">Please <a href="{{ route('login') }}" class="text-red-800 hover:text-red-900 underline">log in</a> to access this page.</span>
                    </div>
                </div>
            @endauth
        </div>
    </main>
</body>
</html>