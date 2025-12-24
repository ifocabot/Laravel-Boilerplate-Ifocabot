@extends('layouts.admin')

@section('title', 'Edit Dokumen - ' . $document->title)

@section('content')
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Dokumen</h1>
                <p class="mt-1 text-sm text-gray-500">Perbarui informasi dokumen karyawan.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hris.documents.download', $document) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download
                </a>
                <a href="{{ route('hris.documents.show', $document) }}"
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
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Edit Dokumen</h3>
                        <p class="text-sm text-indigo-100">{{ $document->title }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('hris.documents.update', $document) }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Current File Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-blue-900">File Saat Ini</div>
                            <div class="text-sm text-blue-700">{{ $document->original_filename }}
                                ({{ $document->file_size_formatted }})</div>
                        </div>
                    </div>
                </div>

                {{-- Employee & Category Selection --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Employee Selection --}}
                    <div>
                        <label for="employee_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Karyawan <span class="text-red-500">*</span>
                        </label>
                        <select name="employee_id" id="employee_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $document->employee_id) == $employee->id ? 'selected' : '' }}>
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
                                    data-max-size="{{ $category->max_file_size_mb }}" {{ old('document_category_id', $document->document_category_id) == $category->id ? 'selected' : '' }}>
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
                            <input type="text" name="title" id="title" required value="{{ old('title', $document->title) }}"
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
                            <input type="text" name="document_number" id="document_number"
                                value="{{ old('document_number', $document->document_number) }}"
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
                            placeholder="Deskripsi dokumen (opsional)">{{ old('description', $document->description) }}</textarea>
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
                            <input type="date" name="document_date" id="document_date"
                                value="{{ old('document_date', $document->document_date?->format('Y-m-d')) }}"
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
                            <input type="date" name="expiry_date" id="expiry_date"
                                value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}"
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
                            <input type="text" name="issuer" id="issuer" value="{{ old('issuer', $document->issuer) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Instansi/lembaga penerbit">
                            @error('issuer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- File Upload (Optional) --}}
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Ganti File (Opsional)</h4>

                    <div>
                        <label for="file" class="block text-sm font-semibold text-gray-700 mb-2">
                            File Dokumen Baru
                        </label>
                        <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 transition-colors"
                            x-data="fileUpload()" @drop.prevent="handleDrop($event)" @dragover.prevent @dragenter.prevent>

                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2">
                                        <span>Upload file baru</span>
                                        <input id="file" name="file" type="file" class="sr-only"
                                            @change="fileSelected($event)">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <div x-show="!selectedFileName" class="text-xs text-gray-500">
                                    Kosongkan jika tidak ingin mengganti file
                                </div>
                                <div x-show="selectedFileName" class="text-sm text-green-600 font-medium"
                                    x-text="selectedFileName"></div>
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
                            <input type="checkbox" name="is_confidential" id="is_confidential" value="1" {{ old('is_confidential', $document->is_confidential) ? 'checked' : '' }}
                                class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1">
                                <label for="is_confidential" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                    Dokumen Rahasia
                                </label>
                                <p class="text-xs text-gray-500 mt-0.5">Dokumen ini memerlukan izin khusus untuk diakses</p>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                                Status
                            </label>
                            <select name="status" id="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }}>
                                    Draft</option>
                                <option value="pending_approval" {{ old('status', $document->status) == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="approved" {{ old('status', $document->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('status', $document->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="expired" {{ old('status', $document->status) == 'expired' ? 'selected' : '' }}>
                                    Expired</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('hris.documents.show', $document) }}"
                        class="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
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

                    init() {
                        // Check if a category is already selected
                        const select = document.getElementById('document_category_id');
                        if (select.value) {
                            this.categoryChanged();
                        }
                    },

                    categoryChanged() {
                        const select = document.getElementById('document_category_id');
                        const option = select.options[select.selectedIndex];

                        if (option.value) {
                            this.selectedCategory = true;
                            this.allowedTypes = option.dataset.allowedTypes?.toUpperCase().replace(/,/g, ', ') || 'Semua tipe';
                            this.maxSize = option.dataset.maxSize || '10';
                        } else {
                            this.selectedCategory = false;
                        }
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
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection