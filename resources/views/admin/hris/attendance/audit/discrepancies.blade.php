@extends('layouts.admin')

@section('title', 'Audit Trail - Discrepancies')

@section('content')
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">🔍 Attendance Audit Trail</h1>
            <p class="text-gray-500">Verify data integrity and view event history</p>
        </div>

        {{-- Filter Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                        class="rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                        class="rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select name="employee_id"
                        class="rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Employees</option>
                        @foreach(\App\Models\Employee::orderBy('full_name')->get() as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Check Discrepancies
                </button>
            </form>
        </div>

        {{-- Results --}}
        @if(count($results) > 0)
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-2 text-orange-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-semibold">{{ count($results) }} employee(s) with discrepancies found</span>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($results as $result)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    {{ $result['employee_name'] ?? 'Employee #' . $result['employee_id'] }}</h3>
                                <p class="text-sm text-gray-500">{{ $result['discrepancy_count'] }} discrepancy(ies)</p>
                            </div>
                            <a href="{{ route('hris.attendance.audit.period', $result['employee_id']) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}"
                                class="text-sm text-indigo-600 hover:text-indigo-800">
                                View Timeline →
                            </a>
                        </div>
                        <div class="p-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b">
                                    <th class="pb-2">Date</th>
                                    <th class="pb-2">Field</th>
                                    <th class="pb-2">Current</th>
                                    <th class="pb-2">Expected</th>
                                    <th class="pb-2">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($result['discrepancies'] as $discrepancy)
                                    @foreach($discrepancy['differences'] as $field => $values)
                                        <tr>
                                            <td class="py-2">{{ $discrepancy['date'] }}</td>
                                            <td class="py-2 font-mono text-xs">{{ $field }}</td>
                                            <td class="py-2 text-red-600">{{ is_array($values) ? ($values['current'] ?? json_encode($values)) : $values }}</td>
                                            <td class="py-2 text-green-600">{{ is_array($values) ? ($values['expected'] ?? '-') : '-' }}</td>
                                            <td class="py-2">
                                                <a href="{{ route('hris.attendance.audit.timeline', [$result['employee_id'], $discrepancy['date']]) }}"
                                                    class="text-indigo-600 hover:text-indigo-800 text-xs">
                                                    View Events
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-green-800">All Clear!</h3>
                <p class="text-green-600">No discrepancies found for the selected period.</p>
            </div>
        @endif

        {{-- Quick Actions --}}
        <div class="mt-8 bg-gray-50 rounded-xl p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('hris.attendance.adjustments.create') }}"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                    ➕ Create Adjustment
                </a>
                <form method="POST" action="{{ route('hris.attendance.audit.rebuild') }}" class="inline"
                    x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm hover:bg-orange-200 transition disabled:opacity-50">
                        🔄 Rebuild Today's Attendance
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection