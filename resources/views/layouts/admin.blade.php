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
    @stack('styles')
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
                <span>{{ config('app.name') }}</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation - Scrollable -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">

            {{-- DASHBOARD --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-2">Main</p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard
            </a>

            {{-- ============================================ --}}
            {{-- EMPLOYEE SELF-SERVICE --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Self Service</p>

            <a href="{{ route('ess.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('ess.dashboard') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Portal Saya
            </a>

            <a href="{{ route('ess.profile.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('ess.profile.*') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profil Saya
            </a>

            <a href="{{ route('ess.leave.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('ess.leave.*') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Pengajuan Cuti
            </a>

            <a href="{{ route('ess.payroll.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('ess.payroll.*') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Slip Gaji
            </a>


            {{-- ============================================ --}}
            {{-- EMPLOYEE MANAGEMENT --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Employee Management
            </p>

            {{-- Organization Structure --}}
            <a href="{{ route('hris.organization.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hris.organization.*') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Org Structure
            </a>

            <div x-data="{ open: {{ request()->routeIs('hris.employees.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.employees.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Employees
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    <a href="{{ route('hris.employees.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.employees.index') && !request()->has('status') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        All Employees
                    </a>

                    <a href="{{ route('hris.employees.index', ['status' => 'active']) }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->get('status') === 'active' ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Active Employees
                    </a>

                    <a href="{{ route('hris.employees.index', ['status' => 'resigned']) }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->get('status') === 'resigned' ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Resigned Employees
                    </a>

                    <a href="{{ route('hris.employees.create') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.employees.create') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Employee
                    </a>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- ATTENDANCE & OVERTIME --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Attendance & Time</p>

            <div x-data="{ open: {{ request()->routeIs('hris.attendance.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.attendance.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Attendance
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>


                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    {{-- Today's Attendance (Quick Access) --}}
                    <a href="{{ route('hris.attendance.logs.today') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.logs.today') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Today's Attendance
                    </a>

                    {{-- Attendance History --}}
                    <a href="{{ route('hris.attendance.logs.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.logs.index') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Attendance History
                    </a>

                    <a href="{{ route('hris.attendance.logs.my-attendance') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.logs.my-attendance') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">

                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Clock In/Out
                    </a>

                    {{-- Attendance Summary --}}
                    <a href="{{ route('hris.attendance.summaries.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.summaries.index') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Attendance Summary
                    </a>

                    <div class="border-t border-gray-200 my-2"></div>

                    {{-- Employee Schedules --}}
                    <a href="{{ route('hris.attendance.schedules.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.schedules.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Employee Schedules
                    </a>

                    {{-- Shifts Management --}}
                    <a href="{{ route('hris.attendance.shifts.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.shifts.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shifts Management
                    </a>
                </div>
            </div>

            {{-- OVERTIME MANAGEMENT --}}
            <div x-data="{ open: {{ request()->routeIs('hris.attendance.overtime.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.attendance.overtime.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Overtime
                        @php
                            $pendingCount = \App\Models\OvertimeRequest::pending()->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="ml-auto px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    {{-- Overtime Approval (Priority) --}}
                    <a href="{{ route('hris.attendance.overtime.approvals') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.overtime.approvals') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Approval Dashboard
                        @if($pendingCount > 0)
                            <span class="ml-auto px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>

                    {{-- Overtime Requests --}}
                    <a href="{{ route('hris.attendance.overtime.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.overtime.index') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        All Requests
                    </a>

                    {{-- Create Request --}}
                    <a href="{{ route('hris.attendance.overtime.create') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.attendance.overtime.create') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Request
                    </a>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- LEAVE MANAGEMENT --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Leave Management</p>

            <div x-data="{ open: {{ request()->routeIs('hris.leave.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.leave.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Leave / Cuti
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    {{-- My Leave Requests (For Users) --}}
                    <a href="{{ route('hris.leave.requests.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.leave.requests.index') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        My Leave Requests
                    </a>

                    {{-- All Requests (For Admin/HR) --}}
                    <a href="{{ route('hris.leave.requests.admin') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.leave.requests.admin') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        All Leave Requests (Admin)
                    </a>

                    {{-- Leave Types Master --}}
                    <a href="{{ route('hris.leave.types.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.leave.types.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Leave Types
                    </a>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- TRAINING & DEVELOPMENT --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Training &
                Development</p>

            <div x-data="{ open: {{ request()->routeIs('hris.training.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.training.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Training
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    <a href="{{ route('hris.training.programs.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.training.programs.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Training Programs
                    </a>
                    <a href="{{ route('hris.training.enrollments.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.training.enrollments.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Enrollments
                    </a>

                    <div class="border-t border-gray-200 my-2"></div>

                    <a href="{{ route('hris.training.skills.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.training.skills.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Skills
                    </a>
                    <a href="{{ route('hris.training.assessments.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.training.assessments.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Skill Assessments
                    </a>
                    <a href="{{ route('hris.training.certifications.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.training.certifications.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Certifications
                    </a>

                    <div class="border-t border-gray-200 my-2"></div>

                    <a href="{{ route('hris.training.trainers.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.training.trainers.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Trainers
                    </a>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- APPROVALS --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Workflows</p>

            {{-- Pending Approvals --}}
            <a href="{{ route('approvals.pending') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('approvals.pending') ? 'text-white bg-orange-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pending Approvals
            </a>

            {{-- Approval Settings (Admin) --}}
            <a href="{{ route('admin.approval-workflows.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.approval-workflows.*') ? 'text-white bg-indigo-600 shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Workflow Settings
            </a>
            {{-- ============================================ --}}
            {{-- PAYROLL --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Payroll & Salary</p>

            <div x-data="{ open: {{ request()->routeIs('hris.payroll.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.payroll.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Payroll
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    {{-- Employee Salary Management --}}
                    <a href="{{ route('hris.payroll.employee-salaries.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.payroll.employee-salaries.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Employee Salaries
                    </a>

                    <div class="border-t border-gray-200 my-2"></div>

                    {{-- Payroll Periods --}}
                    <a href="{{ route('hris.payroll.periods.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.payroll.periods.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Payroll Periods
                    </a>

                    {{-- Payroll Components --}}
                    <a href="{{ route('hris.payroll.components.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.payroll.components.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Salary Components
                    </a>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- DOCUMENT MANAGEMENT --}}
            {{-- ============================================ --}}
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Document Management
            </p>

            <div x-data="{ open: {{ request()->routeIs('hris.documents.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('hris.documents.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        Documents
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    {{-- Employee Documents --}}
                    <a href="{{ route('hris.documents.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.documents.index') || (request()->routeIs('hris.documents.*') && !request()->routeIs('hris.documents.categories.*')) ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Employee Documents
                    </a>

                    {{-- Document Categories --}}
                    <a href="{{ route('hris.documents.categories.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('hris.documents.categories.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        Document Categories
                    </a>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- SETTINGS --}}
            {{-- ============================================ --}}
            <p class=" px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Settings</p>

            {{-- Access Control --}}
            <div x-data="{ open: {{ request()->routeIs('access-control.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('access-control.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
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

                <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                    <a href="{{ route('access-control.dashboard') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.dashboard') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('access-control.users.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.users.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Users
                    </a>
                    <a href="{{ route('access-control.roles.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.roles.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Roles
                    </a>
                    <a href="{{ route('access-control.permissions.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.permissions.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Permissions
                    </a>
                    <a href="{{ route('access-control.audit-logs.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('access-control.audit-logs.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                        Audit Logs
                    </a>
                </div>
            </div>

{{-- Master Data (3 level) --}}
<div x-data="{ open: {{ request()->routeIs('master-data.*') ? 'true' : 'false' }} }">
    {{-- Level 2 (Master Data) --}}
    <button @click="open = !open"
        class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors
        {{ request()->routeIs('master-data.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Master Data
        </div>
        <svg :class="open ? 'rotate-180' : ''"
            class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Level 2 content --}}
    <div x-show="open" x-cloak x-transition class="space-y-1 pl-4 mt-1">

        {{-- Level 3 (General) --}}
        <div x-data="{ open2: {{ request()->routeIs('master-data.general.*') ? 'true' : 'false' }} }">
            <button @click="open2 = !open2"
                class="w-full flex items-center justify-between px-4 py-2 text-sm rounded-lg transition-colors
                {{ request()->routeIs('master-data.general.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4h16v16H4V4z" />
                    </svg>
                    General
                </span>
                <svg :class="open2 ? 'rotate-180' : ''"
                    class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- Level 3 content (links) --}}
            <div x-show="open2" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                <a href="{{ route('master-data.general.departments.index') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors
                    {{ request()->routeIs('master-data.general.departments.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                    Departments
                </a>
                <a href="{{ route('master-data.general.locations.index') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors
                    {{ request()->routeIs('master-data.general.locations.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                    Locations
                </a>
            </div>
        </div>

        {{-- Level 3 (HRIS) --}}
        <div x-data="{ open2: {{ request()->routeIs('master-data.hris.*') ? 'true' : 'false' }} }" class="mt-1">
            <button @click="open2 = !open2"
                class="w-full flex items-center justify-between px-4 py-2 text-sm rounded-lg transition-colors
                {{ request()->routeIs('master-data.hris.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h10M7 12h10M7 17h10" />
                    </svg>
                    HRIS
                </span>
                <svg :class="open2 ? 'rotate-180' : ''"
                    class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open2" x-cloak x-transition class="space-y-1 pl-4 mt-1">
                <a href="{{ route('master-data.hris.levels.index') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors
                    {{ request()->routeIs('master-data.hris.levels.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                    Job Levels
                </a>
                <a href="{{ route('master-data.hris.positions.index') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors
                    {{ request()->routeIs('master-data.hris.positions.*') ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50' }}">
                    Positions
                </a>
            </div>
        </div>

    </div>
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
                <nav class="flex items-center gap-2 text-sm overflow-x-auto whitespace-nowrap">
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

                {{-- Notification Bell --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="relative p-2 text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-600 rounded-full">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                <a href="{{ route('notifications.index') }}"
                                    class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($notification->data['type'] === 'retroactive_overtime_approval')
                                            ⚠️ Retroactive Overtime
                                        @else
                                            🔒 Overtime Blocked
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $notification->data['employee_name'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <p class="text-sm text-gray-500">No new notifications</p>
                                </div>
                            @endforelse
                        </div>
                        <a href="{{ route('notifications.index') }}"
                            class="block px-4 py-3 text-center text-sm font-medium text-indigo-600 hover:bg-gray-50">
                            View All
                        </a>
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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')

</body>

</html>