@extends('layouts.admin')

@section('title', 'Kelola Gaji Karyawan')

@section('content')
    <div class="space-y-6" x-data="salaryManagement()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Gaji Karyawan</h1>
                <p class="text-sm text-gray-500 mt-1">Assign komponen gaji ke karyawan secara terpusat</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openBulkAssignModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Bulk Assign
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="GET" action="{{ route('hris.payroll.employee-salaries.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama atau NIK..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                {{-- Department Filter --}}
                <div>
                    <select name="department_id"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Position Filter --}}
                <div>
                    <select name="position_id"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Semua Posisi</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>
                                {{ $pos->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Filter
                    </button>
                    @if(request('search') || request('department_id') || request('position_id'))
                        <a href="{{ route('hris.payroll.employee-salaries.index') }}"
                            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Employee List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Daftar Karyawan ({{ $employees->total() }})</h2>
            </div>

            @if($employees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="toggleSelectAll($event)"
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Departemen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Komponen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Earnings
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Deductions
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Take Home Pay
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($employees as $employee)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" value="{{ $employee->id }}"
                                            @change="toggleSelect({{ $employee->id }})"
                                            :checked="selectedEmployees.includes({{ $employee->id }})"
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                                {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $employee->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $employee->nik }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ $employee->currentCareer?->department?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $employee->currentCareer?->position?->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                            {{ $employee->activePayrollComponents->count() }} komponen
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-green-600">
                                            Rp {{ number_format($employee->total_earnings, 0, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-red-600">
                                            Rp {{ number_format($employee->total_deductions, 0, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-900">
                                            Rp {{ number_format($employee->net_salary, 0, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openEmployeeModal({{ $employee->id }})" type="button"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 text-indigo-600 hover:bg-indigo-50 text-sm font-medium rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Kelola
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $employees->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-sm">Tidak ada karyawan ditemukan</p>
                </div>
            @endif
        </div>

        {{-- Modals (Bulk Assign, Employee Detail) --}}
        {{-- TODO: Implement modals with Alpine.js --}}
    </div>

    @push('scripts')
        <script>
            function salaryManagement() {
                return {
                    selectedEmployees: [],
                    
                    toggleSelectAll(event) {
                        if (event.target.checked) {
                            this.selectedEmployees = @json($employees->pluck('id'));
                        } else {
                            this.selectedEmployees = [];
                        }
                    },
                    
                    toggleSelect(employeeId) {
                        const index = this.selectedEmployees.indexOf(employeeId);
                        if (index > -1) {
                            this.selectedEmployees.splice(index, 1);
                        } else {
                            this.selectedEmployees.push(employeeId);
                        }
                    },
                    
                    openBulkAssignModal() {
                        if (this.selectedEmployees.length === 0) {
                            alert('Pilih minimal 1 karyawan');
                            return;
                        }
                        // Open modal
                    },
                    
                    openEmployeeModal(employeeId) {
                        // Open employee detail modal
                        window.location.href = `/hris/payroll/employees/${employeeId}/components`;
                    }
                }
            }
        </script>
    @endpush
@endsection