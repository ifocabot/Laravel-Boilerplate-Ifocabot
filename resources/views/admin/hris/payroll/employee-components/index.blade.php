@extends('layouts.admin')

@section('title', 'Komponen Gaji - ' . $employee->full_name)

@section('content')
    <div class="space-y-6" x-data="employeeComponentsPage()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.employees.show', $employee->id) }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Komponen Gaji Karyawan</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $employee->full_name }} ({{ $employee->nik }})</p>
            </div>
        </div>

        {{-- Employee Summary Card --}}
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                        </div>
                        <div class="text-white">
                            <h2 class="text-xl font-bold mb-1">{{ $employee->full_name }}</h2>
                            <p class="text-purple-100 text-sm">{{ $employee->nik }}</p>
                            <p class="text-purple-100 text-sm">
                                {{ $employee->current_position?->name }} - {{ $employee->current_department?->name }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right text-white">
                        <p class="text-purple-100 text-xs mb-1">Take Home Pay</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($netSalary, 0, ',', '.') }}</p>
                        <p class="text-xs text-purple-100 mt-1">
                            ({{ $totalEarnings > 0 ? number_format(($netSalary / $totalEarnings) * 100, 1) : '0' }}% dari gross)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Components</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalComponents }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $activeComponents }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Deductions</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">Rp {{ number_format($totalDeductions, 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Button --}}
        <div class="flex justify-end">
            <button @click="openCreateModal()" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Komponen
            </button>
        </div>

        {{-- Components List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Daftar Komponen Gaji</h2>
            </div>

            @if($employee->payrollComponents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Komponen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Effective Period
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($employee->payrollComponents as $empComponent)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $empComponent->component->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $empComponent->component->code }}</p>
                                            @if($empComponent->notes)
                                                <p class="text-xs text-gray-400 mt-1">{{ $empComponent->notes }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $empComponent->component->type_badge_class }}">
                                                {{ $empComponent->component->type_label }}
                                            </span>
                                            @if($empComponent->is_recurring)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                    Recurring
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-900">{{ $empComponent->formatted_amount }}</p>
                                        <p class="text-xs text-gray-500">{{ $empComponent->unit }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="text-gray-900">{{ $empComponent->effective_from->format('d M Y') }}</p>
                                            <p class="text-xs text-gray-500">
                                                s/d {{ $empComponent->effective_to ? $empComponent->effective_to->format('d M Y') : 'Unlimited' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            {{ $empComponent->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $empComponent->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditModal({{ $empComponent->id }})" type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            @if($empComponent->is_active)
                                                <button @click="openDeactivateModal({{ $empComponent->id }})" type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                                                    title="Deactivate">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </button>
                                            @else
                                                <form action="{{ route('hris.payroll.employee-components.destroy', [$employee->id, $empComponent->id]) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Delete">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Belum ada komponen gaji</p>
                    <p class="text-gray-400 text-sm mb-4">Tambahkan komponen gaji untuk karyawan ini</p>
                    <button @click="openCreateModal()" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Komponen
                    </button>
                </div>
            @endif
        </div>

        {{-- Create Modal --}}
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" 
            @keydown.escape.window="closeCreateModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCreateModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form action="{{ route('hris.payroll.employee-components.store', $employee->id) }}" method="POST">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Tambah Komponen Gaji</h3>
                                        <p class="text-sm text-indigo-100">{{ $employee->full_name }}</p>
                                    </div>
                                </div>
                                <button @click="closeCreateModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6" x-data="{ 
                            selectedComponent: null,
                            components: {
                                @foreach($earningComponents->merge($deductionComponents) as $comp)
                                {{ $comp->id }}: {
                                    calculationType: '{{ $comp->calculation_type }}',
                                    percentageValue: {{ $comp->percentage_value ?? 'null' }},
                                    ratePerDay: {{ $comp->rate_per_day ?? 'null' }},
                                    ratePerHour: {{ $comp->rate_per_hour ?? 'null' }},
                                    notes: '{{ addslashes($comp->calculation_notes ?? '') }}'
                                },
                                @endforeach
                            },
                            selectComponent(id) {
                                this.selectedComponent = this.components[id] || null;
                            },
                            needsAmount() {
                                return !this.selectedComponent || this.selectedComponent.calculationType === 'fixed';
                            }
                        }">
                            {{-- Component Selection --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Pilih Komponen <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-2 max-h-72 overflow-y-auto">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 mb-2 uppercase">Earnings / Pendapatan</p>
                                        @foreach($earningComponents as $component)
                                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer mb-2">
                                                <input type="radio" name="component_id" value="{{ $component->id }}" required
                                                    @change="selectComponent({{ $component->id }})"
                                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $component->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $component->code }}</p>
                                                    @if($component->calculation_type !== 'fixed')
                                                        <p class="text-xs text-indigo-600 mt-1">
                                                            @if($component->calculation_type === 'percentage')
                                                                📊 {{ $component->percentage_value }}% dari Gaji Pokok
                                                            @elseif($component->calculation_type === 'daily_rate')
                                                                📅 Rp {{ number_format($component->rate_per_day ?? 0, 0, ',', '.') }}/hari
                                                            @elseif($component->calculation_type === 'hourly_rate')
                                                                ⏰ Rp {{ number_format($component->rate_per_hour ?? 0, 0, ',', '.') }}/jam
                                                            @endif
                                                        </p>
                                                    @endif
                                                </div>
                                                <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">
                                                    {{ $component->category_label }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="pt-4">
                                        <p class="text-xs font-semibold text-gray-600 mb-2 uppercase">Deductions / Potongan</p>
                                        @foreach($deductionComponents as $component)
                                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer mb-2">
                                                <input type="radio" name="component_id" value="{{ $component->id }}" required
                                                    @change="selectComponent({{ $component->id }})"
                                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $component->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $component->code }}</p>
                                                    @if($component->calculation_type !== 'fixed')
                                                        <p class="text-xs text-indigo-600 mt-1">
                                                            @if($component->calculation_type === 'percentage')
                                                                📊 {{ $component->percentage_value }}% dari Gaji Pokok
                                                            @elseif($component->calculation_type === 'daily_rate')
                                                                📅 Rp {{ number_format($component->rate_per_day ?? 0, 0, ',', '.') }}/hari
                                                            @elseif($component->calculation_type === 'hourly_rate')
                                                                ⏰ Rp {{ number_format($component->rate_per_hour ?? 0, 0, ',', '.') }}/jam
                                                            @endif
                                                        </p>
                                                    @endif
                                                </div>
                                                <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">
                                                    {{ $component->category_label }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Calculation Info Alert --}}
                            <template x-if="selectedComponent && selectedComponent.calculationType !== 'fixed'">
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-blue-900 mb-1">Perhitungan Otomatis</h4>
                                            <p class="text-sm text-blue-700" x-text="selectedComponent.notes || 'Komponen ini dihitung secara otomatis'"></p>
                                            <p class="text-xs text-blue-600 mt-1">
                                                <span x-show="selectedComponent.calculationType === 'percentage'">
                                                    Jumlah = <span x-text="selectedComponent.percentageValue"></span>% × Gaji Pokok
                                                </span>
                                                <span x-show="selectedComponent.calculationType === 'daily_rate'">
                                                    Jumlah = Rp <span x-text="selectedComponent.ratePerDay?.toLocaleString('id')"></span> × Hari Kerja
                                                </span>
                                                <span x-show="selectedComponent.calculationType === 'hourly_rate'">
                                                    Jumlah = Rp <span x-text="selectedComponent.ratePerHour?.toLocaleString('id')"></span> × Jam
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Amount (only for fixed type) --}}
                                <div x-show="needsAmount()">
                                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Jumlah <span class="text-red-500" x-show="needsAmount()">*</span>
                                    </label>
                                    <input type="number" name="amount" id="amount" step="0.01" min="0"
                                        :required="needsAmount()"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="5000000">
                                    <p class="mt-1 text-xs text-gray-500">Jumlah tetap per bulan (IDR)</p>
                                </div>

                                {{-- Auto-calculated notice --}}
                                <div x-show="!needsAmount()">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Jumlah
                                    </label>
                                    <div class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 text-sm">
                                        Dihitung otomatis saat payroll
                                    </div>
                                    <input type="hidden" name="amount" value="0">
                                </div>

                                {{-- Unit --}}
                                <div>
                                    <label for="unit" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Unit
                                    </label>
                                    <input type="text" name="unit" id="unit" value="IDR"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="IDR">
                                </div>

                                {{-- Effective From --}}
                                <div>
                                    <label for="effective_from" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Efektif Dari <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="effective_from" id="effective_from" required
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                {{-- Effective To --}}
                                <div>
                                    <label for="effective_to" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Efektif Sampai
                                    </label>
                                    <input type="date" name="effective_to" id="effective_to"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p class="mt-1 text-xs text-gray-500">Kosongkan untuk unlimited</p>
                                </div>
                            </div>

                            {{-- Is Recurring --}}
                            <div class="flex items-start gap-3">
                                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" checked
                                    class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <label for="is_recurring" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                        Recurring Component
                                    </label>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Komponen ini akan otomatis masuk ke payroll setiap bulan
                                    </p>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Catatan tambahan tentang komponen ini"></textarea>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeCreateModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Simpan Komponen
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" 
            @keydown.escape.window="closeEditModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeEditModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    @foreach($employee->payrollComponents as $empComponent)
                        <form x-show="selectedComponentId === {{ $empComponent->id }}" 
                            action="{{ route('hris.payroll.employee-components.update', [$employee->id, $empComponent->id]) }}" 
                            method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Modal Header --}}
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-white">Edit Komponen Gaji</h3>
                                            <p class="text-sm text-indigo-100">{{ $empComponent->component->name }}</p>
                                        </div>
                                    </div>
                                    <button @click="closeEditModal()" type="button"
                                        class="text-white/80 hover:text-white transition-colors">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Modal Body --}}
                            <div class="px-6 py-6 space-y-6">
                                <input type="hidden" name="component_id" value="{{ $empComponent->component_id }}">

                                {{-- Component Info --}}
                                @if($empComponent->component->calculation_type !== 'fixed')
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                        <div class="flex gap-3">
                                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-blue-900 mb-1">Perhitungan Otomatis</h4>
                                                <p class="text-sm text-blue-700">{{ $empComponent->component->calculation_notes ?? 'Komponen ini dihitung secara otomatis' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Amount (show for fixed, optional for others) --}}
                                    <div>
                                        <label for="edit_amount_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Jumlah 
                                            @if($empComponent->component->calculation_type === 'fixed')
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        @if($empComponent->component->calculation_type === 'fixed')
                                            <input type="number" name="amount" id="edit_amount_{{ $empComponent->id }}" 
                                                required step="0.01" min="0" value="{{ $empComponent->amount }}"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        @else
                                            <div class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 text-sm">
                                                Dihitung otomatis saat payroll
                                            </div>
                                            <input type="hidden" name="amount" value="{{ $empComponent->amount }}">
                                        @endif
                                    </div>

                                    {{-- Unit --}}
                                    <div>
                                        <label for="edit_unit_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Unit
                                        </label>
                                        <input type="text" name="unit" id="edit_unit_{{ $empComponent->id }}" 
                                            value="{{ $empComponent->unit }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>

                                    {{-- Effective From --}}
                                    <div>
                                        <label for="edit_effective_from_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Efektif Dari <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="effective_from" id="edit_effective_from_{{ $empComponent->id }}" 
                                            required value="{{ $empComponent->effective_from->format('Y-m-d') }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>

                                    {{-- Effective To --}}
                                    <div>
                                        <label for="edit_effective_to_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Efektif Sampai
                                        </label>
                                        <input type="date" name="effective_to" id="edit_effective_to_{{ $empComponent->id }}"
                                            value="{{ $empComponent->effective_to?->format('Y-m-d') }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan untuk unlimited</p>
                                    </div>
                                </div>

                                {{-- Is Active --}}
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="is_active" id="edit_is_active_{{ $empComponent->id }}" 
                                        value="1" {{ $empComponent->is_active ? 'checked' : '' }}
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="edit_is_active_{{ $empComponent->id }}" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            Active Component
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Komponen ini akan digunakan dalam perhitungan payroll
                                        </p>
                                    </div>
                                </div>

                                {{-- Is Recurring --}}
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="is_recurring" id="edit_is_recurring_{{ $empComponent->id }}" 
                                        value="1" {{ $empComponent->is_recurring ? 'checked' : '' }}
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="edit_is_recurring_{{ $empComponent->id }}" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            Recurring Component
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Komponen ini akan otomatis masuk ke payroll setiap bulan
                                        </p>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="edit_notes_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Catatan
                                    </label>
                                    <textarea name="notes" id="edit_notes_{{ $empComponent->id }}" rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Catatan tambahan tentang komponen ini">{{ $empComponent->notes }}</textarea>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                                <button @click="closeEditModal()" type="button"
                                    class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Update Komponen
                                    </span>
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Deactivate Modal --}}
        <div x-show="showDeactivateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" 
            @keydown.escape.window="closeDeactivateModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeactivateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeDeactivateModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showDeactivateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    @foreach($employee->payrollComponents as $empComponent)
                        <form x-show="selectedComponentId === {{ $empComponent->id }}" 
                            action="{{ route('hris.payroll.employee-components.deactivate', [$employee->id, $empComponent->id]) }}" 
                            method="POST">
                            @csrf

                            {{-- Modal Header --}}
                            <div class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-white">Nonaktifkan Komponen</h3>
                                            <p class="text-sm text-yellow-100">{{ $empComponent->component->name }}</p>
                                        </div>
                                    </div>
                                    <button @click="closeDeactivateModal()" type="button"
                                        class="text-white/80 hover:text-white transition-colors">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Modal Body --}}
                            <div class="px-6 py-6 space-y-6">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-yellow-900 mb-1">Perhatian</h4>
                                            <p class="text-sm text-yellow-700">
                                                Komponen ini akan dinonaktifkan dan tidak akan digunakan dalam perhitungan payroll setelah tanggal yang ditentukan.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Effective To --}}
                                <div>
                                    <label for="deactivate_effective_to_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tanggal Berakhir <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="effective_to" id="deactivate_effective_to_{{ $empComponent->id }}" 
                                        required min="{{ $empComponent->effective_from->format('Y-m-d') }}"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <p class="mt-1 text-xs text-gray-500">
                                        Minimal: {{ $empComponent->effective_from->format('d M Y') }}
                                    </p>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="deactivate_notes_{{ $empComponent->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Alasan Penonaktifan
                                    </label>
                                    <textarea name="notes" id="deactivate_notes_{{ $empComponent->id }}" rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                        placeholder="Jelaskan alasan penonaktifan komponen ini..."></textarea>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                                <button @click="closeDeactivateModal()" type="button"
                                    class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Nonaktifkan
                                    </span>
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function employeeComponentsPage() {
                return {
                    showCreateModal: false,
                    showEditModal: false,
                    showDeactivateModal: false,
                    selectedComponentId: null,

                    openCreateModal() {
                        this.showCreateModal = true;
                    },

                    closeCreateModal() {
                        this.showCreateModal = false;
                    },

                    openEditModal(id) {
                        this.selectedComponentId = id;
                        this.showEditModal = true;
                    },

                    closeEditModal() {
                        this.showEditModal = false;
                        this.selectedComponentId = null;
                    },

                    openDeactivateModal(id) {
                        this.selectedComponentId = id;
                        this.showDeactivateModal = true;
                    },

                    closeDeactivateModal() {
                        this.showDeactivateModal = false;
                        this.selectedComponentId = null;
                    }
                }
            }
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection