@extends('layouts.admin')

@section('title', 'Upload Dokumen Karyawan')

@section('content')
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Upload Dokumen Karyawan</h1>
                <p class="mt-1 text-sm text-gray-500">Upload dokumen baru untuk karyawan.</p>
            </div>
            <div>
                <a href="{{ route('hris.documents.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- Main Form Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="documentUpload()">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-8">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Upload Dokumen Baru</h3>
                        <p class="text-sm text-indigo-100">Pilih karyawan, kategori, dan upload file dokumen</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('hris.documents.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                {{-- Employee & Category Selection --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Employee Selection --}}
                    <div>
                        <label for="employee_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Karyawan <span class="text-red-500">*</span>
                        </label>
                        <select name="employee_id" id="employee_id" required @change="selectedEmployeeChanged()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', request('employee_id')) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->full_name }} ({{ $employee->nik }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Document Category --}}
                    <div>
                        <label for="document_category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Kategori Dokumen <span class="text-red-500">*</span>
                        </label>
                        <select name="document_category_id" id="document_category_id" required @change="categoryChanged()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    data-allowed-types="{{ implode(',', $category->allowed_file_types ?? []) }}"
                                    data-max-size="{{ $category->max_file_size_mb }}"
                                    {{ old('document_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->full_path }}
                                </option>
                            @endforeach
                        </select>
                        @error('document_category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div x-show="selectedCategory" x-cloak class="mt-2 text-xs text-gray-500">
                            <div x-show="allowedTypes">Tipe file diizinkan: <span x-text="allowedTypes"></span></div>
                            <div x-show="maxSize">Ukuran maksimal: <span x-text="maxSize"></span> MB</div>
                        </div>
                    </div>
                </div>

                {{-- Document Information --}}
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Informasi Dokumen</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Document Title --}}
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                Judul Dokumen <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" required value="{{ old('title') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Masukkan judul dokumen">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Document Number --}}
                        <div>
                            <label for="document_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nomor Dokumen
                            </label>
                            <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Nomor referensi dokumen (opsional)">
                            @error('document_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Deskripsi dokumen (opsional)">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Document Date --}}
                        <div>
                            <label for="document_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Dokumen
                            </label>
                            <input type="date" name="document_date" id="document_date" value="{{ old('document_date') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('document_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Expiry Date --}}
                        <div>
                            <label for="expiry_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Kadaluarsa
                            </label>
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('expiry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Issuer --}}
                        <div>
                            <label for="issuer" class="block text-sm font-semibold text-gray-700 mb-2">
                                Penerbit
                            </label>
                            <input type="text" name="issuer" id="issuer" value="{{ old('issuer') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Instansi/lembaga penerbit">
                            @error('issuer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- File Upload --}}
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Upload File</h4>

                    <div>
                        <label for="file" class="block text-sm font-semibold text-gray-700 mb-2">
                            File Dokumen <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 transition-colors"
                            x-data="fileUpload()" @drop.prevent="handleDrop($event)" @dragover.prevent @dragenter.prevent>

                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2">
                                        <span>Upload file</span>
                                        <input id="file" name="file" type="file" class="sr-only" required @change="fileSelected($event)">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <div x-show="!selectedFileName" class="text-xs text-gray-500">
                                    <span x-text="allowedTypesText">PNG, JPG, PDF hingga 10MB</span>
                                </div>
                                <div x-show="selectedFileName" class="text-sm text-green-600 font-medium" x-text="selectedFileName"></div>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Settings --}}
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Pengaturan</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Confidential --}}
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="is_confidential" id="is_confidential" value="1" {{ old('is_confidential') ? 'checked' : '' }}
                                class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1">
                                <label for="is_confidential" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                    Dokumen Rahasia
                                </label>
                                <p class="text-xs text-gray-500 mt-0.5">Dokumen ini memerlukan izin khusus untuk diakses</p>
                            </div>
                        </div>

                        {{-- Notify Expiry --}}
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="notify_expiry" id="notify_expiry" value="1" {{ old('notify_expiry') ? 'checked' : '' }}
                                class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @change="toggleNotifySettings()">
                            <div class="flex-1">
                                <label for="notify_expiry" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                    Notifikasi Kadaluarsa
                                </label>
                                <p class="text-xs text-gray-500 mt-0.5">Kirim notifikasi sebelum dokumen kadaluarsa</p>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Days --}}
                    <div x-show="document.getElementById('notify_expiry').checked" x-cloak>
                        <label for="notify_days_before" class="block text-sm font-semibold text-gray-700 mb-2">
                            Notifikasi Berapa Hari Sebelum Kadaluarsa
                        </label>
                        <input type="number" name="notify_days_before" id="notify_days_before" min="1" max="365" value="{{ old('notify_days_before', 30) }}"
                            class="w-full md:w-48 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('notify_days_before')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('hris.documents.index') }}"
                        class="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            Upload Dokumen
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function documentUpload() {
                return {
                    selectedCategory: false,
                    allowedTypes: '',
                    maxSize: '',
                    allowedTypesText: 'PNG, JPG, PDF hingga 10MB',

                    categoryChanged() {
                        const select = document.getElementById('document_category_id');
                        const option = select.options[select.selectedIndex];

                        if (option.value) {
                            this.selectedCategory = true;
                            this.allowedTypes = option.dataset.allowedTypes?.toUpperCase().replace(/,/g, ', ') || 'Semua tipe';
                            this.maxSize = option.dataset.maxSize || '10';
                            this.allowedTypesText = `${this.allowedTypes} hingga ${this.maxSize}MB`;
                        } else {
                            this.selectedCategory = false;
                            this.allowedTypesText = 'PNG, JPG, PDF hingga 10MB';
                        }
                    },

                    selectedEmployeeChanged() {
                        // You can add logic here if needed when employee changes
                    }
                }
            }

            function fileUpload() {
                return {
                    selectedFileName: '',

                    fileSelected(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.selectedFileName = file.name;
                        }
                    },

                    handleDrop(event) {
                        const files = event.dataTransfer.files;
                        if (files.length > 0) {
                            const file = files[0];
                            document.getElementById('file').files = files;
                            this.selectedFileName = file.name;
                        }
                    }
                }
            }

            function toggleNotifySettings() {
                // This will be handled by Alpine's x-show directive
            }
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection