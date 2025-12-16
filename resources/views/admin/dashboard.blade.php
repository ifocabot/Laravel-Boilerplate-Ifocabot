@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Card 1 -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Employees</p>
                <h3 class="text-2xl font-bold text-gray-800">1,234</h3>
                <span class="text-xs text-green-500 font-medium">+12% from last month</span>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Deliveries Today</p>
                <h3 class="text-2xl font-bold text-gray-800">85</h3>
                <span class="text-xs text-green-500 font-medium">+5% from yesterday</span>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
                <h3 class="text-2xl font-bold text-gray-800">12</h3>
                <span class="text-xs text-orange-500 font-medium">Attention needed</span>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">System Status</p>
                <h3 class="text-2xl font-bold text-gray-800">Normal</h3>
                <span class="text-xs text-gray-400">Up for 15 days</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Charts Section (Placeholder) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Recent Deliveries</h2>
                <button class="text-indigo-600 text-sm font-medium hover:underline">View All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Tracking ID</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Recipient</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">#TRK-9821</td>
                            <td class="px-6 py-4 text-sm text-gray-600">Sarah Connor</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Delivered
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">Today, 10:42 AM</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">#TRK-9820</td>
                            <td class="px-6 py-4 text-sm text-gray-600">John Doe</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    In Transit
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">Today, 09:15 AM</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">#TRK-9819</td>
                            <td class="px-6 py-4 text-sm text-gray-600">Jane Smith</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">Yesterday, 04:30 PM</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">#TRK-9818</td>
                            <td class="px-6 py-4 text-sm text-gray-600">Michael Scott</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Delivered
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">Yesterday, 02:15 PM</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Side Widget -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3 flex-1">
                <button
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New Delivery
                </button>
                <button
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Add Employee
                </button>
                <button
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Report
                </button>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-800 mb-2">System Health</h4>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-500">Server Load</span>
                            <span class="font-medium text-gray-700">45%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-500">Disk Usage</span>
                            <span class="font-medium text-gray-700">72%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full" style="width: 72%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection