<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

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

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900 flex h-screen overflow-hidden"
    x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden"
        @click="sidebarOpen = false" x-cloak></div>

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:shadow-none border-r border-gray-200 flex flex-col">

        <!-- Sidebar Header - Fixed -->
        <div class="flex-none flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <a href="#" class="flex items-center gap-2 font-bold text-2xl text-indigo-600">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>HAHA<span class="text-gray-800">HIHI</span></span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation - Scrollable -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-2">Menu</p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'opacity-90' : '' }}" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard
            </a>

            <a href="#"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Menu
            </a>

            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Settings</p>

            <!-- Access Control Dropdown -->
            <div x-data="{ open: {{ request()->routeIs('access-control.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('access-control.*') ? 'text-indigo-600 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Access Control
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95" class="space-y-1 pl-4 mt-1"
                    style="display: none;">
                    <a href="{{ route('access-control.dashboard') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.dashboard*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('access-control.roles.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.roles.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Roles
                    </a>
                    <a href="{{ route('access-control.permissions.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.permissions.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Permissions
                    </a>
                    <a href="{{ route('access-control.users.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.user.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Users
                    </a>
                    <a href="{{ route('access-control.audit-logs.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.audit-logs.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Audit Logs
                    </a>
                </div>
                <a href="#"
                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stro ke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Menu
                </a>
            </div>
        </nav>

        <!-- User Control Panel - Fixed Section -->
        <div class="flex-none border-t border-gray-100" x-data="{ userMenuOpen: false }">
            <!-- Settings -->
            <a href="#"
                class="flex items-center gap-3 px-8 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>

            <!-- User Profile Button dengan Dropdown - RELATIVE WRAPPER -->
            <div class="px-4 pb-3 relative">
                <!-- Dropdown Menu - Muncul ke Atas dengan Z-INDEX TINGGI -->
                <div x-show="userMenuOpen" @click.away="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 translate-y-2"
                    x-transition:enter-end="transform opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 translate-y-2" style="display: none;"
                    class="absolute bottom-full left-4 right-4 mb-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-[100]">

                    <a href="#"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        My Profile
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Account Settings
                    </a>

                    <div class="border-t border-gray-100 my-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>

                <!-- User Button -->
                <button @click="userMenuOpen = !userMenuOpen"
                    class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all relative z-10">
                    <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <p class="font-semibold text-gray-800 truncate text-xs leading-tight">{{ Auth::user()->name }}
                        </p>
                        <p class="text-[10px] text-gray-500 truncate leading-tight">{{ Auth::user()->email }}</p>
                    </div>
                    <svg :class="userMenuOpen ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 text-gray-400 transition-transform duration-200" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full overflow-hidden lg:ml-0">
        <!-- Top Header -->
        <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
            <div class="flex items-center gap-4">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" class="p-1 text-gray-400 hover:text-gray-600 lg:hidden">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm">
                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Home</a>
                    @php
                        $segments = request()->segments();
                        $path = '';
                    @endphp

                    @foreach ($segments as $segment)
                        @php
                            $path .= '/' . $segment;
                            $displaySegment = Str::title(str_replace(['-', '_'], ' ', $segment));
                        @endphp
                        <span class="text-gray-400">/</span>
                        <a href="{{ url($path) }}" class="text-gray-500 hover:text-gray-700">
                            {{ $displaySegment }}
                        </a>
                    @endforeach

                    @if (!empty($segments) && trim(strip_tags(View::yieldContent('title', ''))) !== $displaySegment)
                        {{-- Only show the yielded title if it's different from the last segment --}}
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-900 font-medium">@yield('title', 'Dashboard')</span>
                    @elseif (empty($segments))
                        {{-- If no segments, just show the default title --}}
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-900 font-medium">@yield('title', 'Dashboard')</span>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-3">
                <!-- Search Bar -->
                <div class="relative hidden md:block">
                    <input type="text" placeholder="Search employees, documents..."
                        class="w-64 lg:w-80 pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Notifications Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-50">
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95" style="display: none;"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-50">

                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                <span class="px-2 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">3
                                    new</span>
                            </div>
                        </div>

                        <!-- Notification List -->
                        <div class="max-h-96 overflow-y-auto">
                            <!-- Notification Item 1 -->
                            <a href="#"
                                class="block px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">New employee onboarding</p>
                                        <p class="text-xs text-gray-500 mt-1">John Doe has completed onboarding process
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full block"></span>
                                    </div>
                                </div>
                            </a>

                            <!-- Notification Item 2 -->
                            <a href="#"
                                class="block px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">Leave request approved</p>
                                        <p class="text-xs text-gray-500 mt-1">Your leave request for Dec 20-22 has been
                                            approved</p>
                                        <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="w-2 h-2 bg-green-500 rounded-full block"></span>
                                    </div>
                                </div>
                            </a>

                            <!-- Notification Item 3 -->
                            <a href="#"
                                class="block px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">Timesheet reminder</p>
                                        <p class="text-xs text-gray-500 mt-1">Please submit your timesheet for this week
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">1 day ago</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full block"></span>
                                    </div>
                                </div>
                            </a>

                            <!-- Notification Item 4 - Read -->
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 opacity-60">
                                        <p class="text-sm font-medium text-gray-900">Team meeting scheduled</p>
                                        <p class="text-xs text-gray-500 mt-1">Weekly sync meeting on Monday at 10 AM</p>
                                        <p class="text-xs text-gray-400 mt-1">2 days ago</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-3 border-t border-gray-100">
                            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Dark Mode Toggle -->
                <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>