@extends('layouts.admin')

@section('title', 'Master Komponen Payroll')

@section('content')
    <div class="space-y-6" x-data="componentsPage()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Master Komponen Payroll</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola komponen pendapatan dan potongan gaji</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hris.payroll.periods.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Periode Payroll
                </a>
                <button @click="openCreateModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Komponen
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Komponen</p>
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
                        <p class="text-sm font-medium text-gray-500">Earnings</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $earningsCount }}</h3>
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
                        <p class="text-sm font-medium text-gray-500">Deductions</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $deductionsCount }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button @click="activeTab = 'earnings'"
                        :class="activeTab === 'earnings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Earnings / Pendapatan
                    </button>
                    <button @click="activeTab = 'deductions'"
                        :class="activeTab === 'deductions' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Deductions / Potongan
                    </button>
                </nav>
            </div>

            {{-- Earnings Tab --}}
            <div x-show="activeTab === 'earnings'" x-cloak class="p-6">
                <div class="space-y-3">
                    @foreach($earnings as $component)
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-100 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $component->name }}</h3>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $component->type_badge_class }}">
                                        {{ $component->type_label }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $component->category_label }}
                                    </span>
                                    @if(!$component->is_active)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">{{ $component->code }}</p>
                                @if($component->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ $component->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span>Calculation: {{ ucfirst($component->calculation_type) }}</span>
                                    @if($component->is_taxable)
                                        <span class="text-orange-600">• Taxable</span>
                                    @endif
                                    @if($component->is_bpjs_base)
                                        <span class="text-blue-600">• BPJS Base</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal({{ $component->id }})" type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form action="{{ route('hris.payroll.components.destroy', $component->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    @if($earnings->count() === 0)
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-sm">Belum ada komponen earnings</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Deductions Tab --}}
            <div x-show="activeTab === 'deductions'" x-cloak class="p-6">
                <div class="space-y-3">
                    @foreach($deductions as $component)
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-100 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $component->name }}</h3>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $component->type_badge_class }}">
                                        {{ $component->type_label }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $component->category_label }}
                                    </span>
                                    @if(!$component->is_active)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">{{ $component->code }}</p>
                                @if($component->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ $component->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span>Calculation: {{ ucfirst($component->calculation_type) }}</span>
                                    @if($component->calculation_formula)
                                        <span class="text-blue-600">• Formula: {{ $component->calculation_formula }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal({{ $component->id }})" type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form action="{{ route('hris.payroll.components.destroy', $component->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    @if($deductions->count() === 0)
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-sm">Belum ada komponen deductions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Create/Edit Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="closeModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                    {{-- Create Form --}}
                    <form x-show="modalMode === 'create'" action="{{ route('hris.payroll.components.store') }}"
                        method="POST">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Tambah Komponen Payroll</h3>
                                        <p class="text-sm text-indigo-100">Buat komponen pendapatan atau potongan baru</p>
                                    </div>
                                </div>
                                <button @click="closeModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6 max-h-[calc(100vh-300px)] overflow-y-auto">
                            {{-- Basic Info --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="create_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kode <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="code" id="create_code" required maxlength="50"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="e.g., BASIC_SALARY">
                                </div>

                                <div>
                                    <label for="create_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nama <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="create_name" required maxlength="150"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="e.g., Gaji Pokok">
                                </div>
                            </div>

                            <div>
                                <label for="create_description" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Deskripsi
                                </label>
                                <textarea name="description" id="create_description" rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Deskripsi komponen"></textarea>
                            </div>

                            {{-- Type & Category --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="create_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tipe <span class="text-red-500">*</span>
                                    </label>
                                    <select name="type" id="create_type" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="">Pilih Tipe</option>
                                        <option value="earning">Earning / Pendapatan</option>
                                        <option value="deduction">Deduction / Potongan</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="create_category" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kategori <span class="text-red-500">*</span>
                                    </label>
                                    <select name="category" id="create_category" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="">Pilih Kategori</option>
                                        <option value="basic_salary">Basic Salary</option>
                                        <option value="fixed_allowance">Fixed Allowance</option>
                                        <option value="variable_allowance">Variable Allowance</option>
                                        <option value="statutory">Statutory</option>
                                        <option value="other_deduction">Other Deduction</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Calculation --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="create_calculation_type"
                                        class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tipe Perhitungan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="calculation_type" id="create_calculation_type" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="">Pilih Tipe</option>
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="formula">Formula</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="create_calculation_formula"
                                        class="block text-sm font-semibold text-gray-700 mb-2">
                                        Formula
                                    </label>
                                    <input type="text" name="calculation_formula" id="create_calculation_formula"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="e.g., basic_salary * 0.05">
                                </div>
                            </div>

                            <div>
                                <label for="create_display_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Urutan Tampilan
                                </label>
                                <input type="number" name="display_order" id="create_display_order" min="0"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="0">
                            </div>

                            {{-- Flags --}}
                            <div class="space-y-3">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="is_taxable" id="create_is_taxable" value="1"
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="create_is_taxable"
                                            class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            Taxable
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">Komponen ini akan dikenakan pajak</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="is_bpjs_base" id="create_is_bpjs_base" value="1"
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="create_is_bpjs_base"
                                            class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            BPJS Base
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">Komponen ini menjadi dasar perhitungan BPJS
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="show_on_slip" id="create_show_on_slip" value="1" checked
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="create_show_on_slip"
                                            class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            Tampilkan di Slip Gaji
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">Komponen ini akan ditampilkan di slip gaji
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Simpan Komponen
                                </span>
                            </button>
                        </div>
                    </form>

                    {{-- Edit Form --}}
                    @foreach(array_merge($earnings->all(), $deductions->all()) as $component)
                        <form x-show="modalMode === 'edit' && selectedComponentId === {{ $component->id }}"
                            action="{{ route('hris.payroll.components.update', $component->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Modal Header --}}
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-white">Edit Komponen Payroll</h3>
                                            <p class="text-sm text-indigo-100">{{ $component->name }}</p>
                                        </div>
                                    </div>
                                    <button @click="closeModal()" type="button"
                                        class="text-white/80 hover:text-white transition-colors">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Modal Body --}}
                            <div class="px-6 py-6 space-y-6 max-h-[calc(100vh-300px)] overflow-y-auto">
                                {{-- Basic Info --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="edit_code_{{ $component->id }}"
                                            class="block text-sm font-semibold text-gray-700 mb-2">
                                            Kode <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="code" id="edit_code_{{ $component->id }}" required
                                            maxlength="50" value="{{ $component->code }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label for="edit_name_{{ $component->id }}"
                                            class="block text-sm font-semibold text-gray-700 mb-2">
                                            Nama <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="name" id="edit_name_{{ $component->id }}" required
                                            maxlength="150" value="{{ $component->name }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                </div>

                                <div>
                                    <label for="edit_description_{{ $component->id }}"
                                        class="block text-sm font-semibold text-gray-700 mb-2">
                                        Deskripsi
                                    </label>
                                    <textarea name="description" id="edit_description_{{ $component->id }}" rows="2"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ $component->description }}</textarea>
                                </div>

                                {{-- Type & Category --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="edit_type_{{ $component->id }}"
                                            class="block text-sm font-semibold text-gray-700 mb-2">
                                            Tipe <span class="text-red-500">*</span>
                                        </label>
                                        <select name="type" id="edit_type_{{ $component->id }}" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="earning" {{ $component->type === 'earning' ? 'selected' : '' }}>Earning
                                                / Pendapatan</option>
                                            <option value="deduction" {{ $component->type === 'deduction' ? 'selected' : '' }}>
                                                Deduction / Potongan</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="edit_category_{{ $component->id }}"
                                            class="block text-sm font-semibold text-gray-700 mb-2">
                                            Kategori <span class="text-red-500">*</span>
                                        </label>
                                        <select name="category" id="edit_category_{{ $component->id }}" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="basic_salary" {{ $component->category === 'basic_salary' ? 'selected' : '' }}>Basic Salary</option>
                                            <option value="fixed_allowance" {{ $component->category === 'fixed_allowance' ? 'selected' : '' }}>Fixed Allowance</option>
                                            <option value="variable_allowance" {{ $component->category === 'variable_allowance' ? 'selected' : '' }}>Variable Allowance</option>
                                            <option value="statutory" {{ $component->category === 'statutory' ? 'selected' : '' }}>Statutory</option>
                                            <option value="other_deduction" {{ $component->category === 'other_deduction' ? 'selected' : '' }}>Other Deduction</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Calculation --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="edit_calculation_type_{{ $component->id }}"
                                            class="block text-sm font-semibold text-gray-700 mb-2">
                                            Tipe Perhitungan <span class="text-red-500">*</span>
                                        </label>
                                        <select name="calculation_type" id="edit_calculation_type_{{ $component->id }}" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="fixed" {{ $component->calculation_type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            <option value="percentage" {{ $component->calculation_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                            <option value="formula" {{ $component->calculation_type === 'formula' ? 'selected' : '' }}>Formula</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="edit_calculation_formula_{{ $component->id }}"
                                            class="block text-sm font-semibold text-gray-700 mb-2">
                                            Formula
                                        </label>
                                        <input type="text" name="calculation_formula"
                                            id="edit_calculation_formula_{{ $component->id }}"
                                            value="{{ $component->calculation_formula }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                </div>

                                <div>
                                    <label for="edit_display_order_{{ $component->id }}"
                                        class="block text-sm font-semibold text-gray-700 mb-2">
                                        Urutan Tampilan
                                    </label>
                                    <input type="number" name="display_order" id="edit_display_order_{{ $component->id }}"
                                        min="0" value="{{ $component->display_order }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                {{-- Flags --}}
                                <div class="space-y-3">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="is_taxable" id="edit_is_taxable_{{ $component->id }}"
                                            value="1" {{ $component->is_taxable ? 'checked' : '' }}
                                            class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="flex-1">
                                            <label for="edit_is_taxable_{{ $component->id }}"
                                                class="text-sm font-semibold text-gray-900 cursor-pointer">
                                                Taxable
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">Komponen ini akan dikenakan pajak</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="is_bpjs_base" id="edit_is_bpjs_base_{{ $component->id }}"
                                            value="1" {{ $component->is_bpjs_base ? 'checked' : '' }}
                                            class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="flex-1">
                                            <label for="edit_is_bpjs_base_{{ $component->id }}"
                                                class="text-sm font-semibold text-gray-900 cursor-pointer">
                                                BPJS Base
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">Komponen ini menjadi dasar perhitungan BPJS
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="show_on_slip" id="edit_show_on_slip_{{ $component->id }}"
                                            value="1" {{ $component->show_on_slip ? 'checked' : '' }}
                                            class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="flex-1">
                                            <label for="edit_show_on_slip_{{ $component->id }}"
                                                class="text-sm font-semibold text-gray-900 cursor-pointer">
                                                Tampilkan di Slip Gaji
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">Komponen ini akan ditampilkan di slip gaji
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="is_active" id="edit_is_active_{{ $component->id }}"
                                            value="1" {{ $component->is_active ? 'checked' : '' }}
                                            class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="flex-1">
                                            <label for="edit_is_active_{{ $component->id }}"
                                                class="text-sm font-semibold text-gray-900 cursor-pointer">
                                                Active
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">Komponen ini aktif dan dapat digunakan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                                <button @click="closeModal()" type="button"
                                    class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
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

    </div>

    @push('scripts')
        <script>
            function componentsPage() {
                return {
                    activeTab: 'earnings',
                    showModal: false,
                    modalMode: 'create',
                    selectedComponentId: null,

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.selectedComponentId = null;
                        this.showModal = true;
                    },

                    openEditModal(id) {
                        this.modalMode = 'edit';
                        this.selectedComponentId = id;
                        this.showModal = true;
                        // Load component data for editing
                    },

                    closeModal() {
                        this.showModal = false;
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