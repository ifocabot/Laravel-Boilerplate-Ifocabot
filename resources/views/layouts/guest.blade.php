<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased bg-white">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
        <!-- Left Side: Form -->
        <div class="flex flex-col justify-center items-center p-8 sm:p-12 lg:p-16 xl:p-24 bg-white relative">
            <div class="w-full max-w-md space-y-8">
                <!-- Mobile Logo (Optional, or just header) -->
                <div class="flex items-center gap-3 mb-8">
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg transform -rotate-6">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">HAHA<span
                            class="text-indigo-600">HIHI</span></h1>
                </div>

                <!-- Content Slot -->
                @yield('content')

                <div class="mt-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} HAHAHRMS. All rights reserved.
                </div>
            </div>
        </div>

        <!-- Right Side: Image -->
        <div class="hidden lg:block relative overflow-hidden bg-indigo-50">
            <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80"
                alt="Office" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-indigo-900/40 mix-blend-multiply"></div>
            <div
                class="absolute bottom-0 left-0 right-0 p-12 text-white bg-gradient-to-t from-gray-900/80 to-transparent">
                <blockquote class="text-lg font-medium italic mb-2">
                    "Manage your workforce with efficiency and style. The future of HR operations starts here."
                </blockquote>
                <p class="text-sm font-semibold opacity-80">- HAHA Team</p>
            </div>
        </div>
    </div>
</body>

</html>