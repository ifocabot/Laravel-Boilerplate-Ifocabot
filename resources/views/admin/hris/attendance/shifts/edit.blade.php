@extends('layouts.admin')

@section('title', 'Edit Shift - ' . $shift->name)

@section('content')
    <div class="space-y-6" x-data="shiftForm()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.shifts.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Shift</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $shift->name }} ({{ $shift->code }})</p>
            </div>
        </div>

        <form action="{{ route('hris.attendance.shifts.update', $shift->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 space-y-6">
                    {{-- Display Errors --}}
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-red-900 mb-1">Terjadi Kesalahan</h4>
                                    <ul class="text-xs text-red-700 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Basic Information --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Shift <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required
                                    value="{{ old('name', $shift->name) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Code --}}
                            <div>
                                <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Kode Shift <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="code" id="code" required
                                    value="{{ old('code', $shift->code) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('code') border-red-500 @enderror">
                                @error('code')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div>
                                <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tipe Shift <span class="text-red-500">*</span>
                                </label>
                                <select name="type" id="type" required x-model="shiftType"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('type') border-red-500 @enderror">
                                    <option value="">Pilih Tipe</option>
                                    <option value="fixed" {{ old('type', $shift->type) == 'fixed' ? 'selected' : '' }}>Fixed - Jam Tetap</option>
                                    <option value="flexible" {{ old('type', $shift->type) == 'flexible' ? 'selected' : '' }}>Flexible - Jam Fleksibel</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Work Hours Required --}}
                            <div>
                                <label for="work_hours_required" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Durasi Kerja Wajib (Menit) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="work_hours_required" id="work_hours_required" required
                                    value="{{ old('work_hours_required', $shift->work_hours_required) }}"
                                    min="0" max="1440" step="30"
                                    x-model="workMinutes"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('work_hours_required') border-red-500 @enderror">
                                <p class="mt-1.5 text-xs text-gray-500">
                                    <span x-text="formatWorkHours(workMinutes)"></span>
                                </p>
                                @error('work_hours_required')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Time Schedule --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Jadwal Waktu</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Start Time --}}
                            <div>
                                <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jam Masuk <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="start_time" id="start_time" required
                                    value="{{ old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i')) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('start_time') border-red-500 @enderror">
                                @error('start_time')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- End Time --}}
                            <div>
                                <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jam Pulang <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="end_time" id="end_time" required
                                    value="{{ old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i')) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('end_time') border-red-500 @enderror">
                                @error('end_time')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Break Start --}}
                            <div>
                                <label for="break_start" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Mulai Istirahat
                                </label>
                                <input type="time" name="break_start" id="break_start"
                                    value="{{ old('break_start', $shift->break_start ? \Carbon\Carbon::parse($shift->break_start)->format('H:i') : '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('break_start') border-red-500 @enderror">
                                @error('break_start')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Break End --}}
                            <div>
                                <label for="break_end" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Selesai Istirahat
                                </label>
                                <input type="time" name="break_end" id="break_end"
                                    value="{{ old('break_end', $shift->break_end ? \Carbon\Carbon::parse($shift->break_end)->format('H:i') : '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('break_end') border-red-500 @enderror">
                                @error('break_end')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Settings --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Pengaturan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Late Tolerance --}}
                            <div>
                                <label for="late_tolerance_minutes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Toleransi Terlambat (Menit) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="late_tolerance_minutes" id="late_tolerance_minutes" required
                                    value="{{ old('late_tolerance_minutes', $shift->late_tolerance_minutes) }}"
                                    min="0" max="120" step="5"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('late_tolerance_minutes') border-red-500 @enderror">
                                @error('late_tolerance_minutes')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Status Shift
                                </label>
                                <div class="flex items-center gap-3 mt-3">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $shift->is_active) ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_active" class="text-sm text-gray-700 cursor-pointer">
                                        Aktif (dapat digunakan untuk attendance)
                                    </label>
                                </div>
                            </div>

                            {{-- Overnight Shift --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Shift Melewati Tengah Malam?
                                </label>
                                <div class="flex items-center gap-3 mt-3">
                                    <input type="checkbox" name="is_overnight" id="is_overnight" value="1"
                                        {{ old('is_overnight', $shift->is_overnight) ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_overnight" class="text-sm text-gray-700 cursor-pointer">
                                        Ya, shift ini melewati tengah malam
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Working Days --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Hari Kerja</h3>
                        <div class="space-y-4">
                            <p class="text-sm text-gray-600">Pilih hari-hari kerja untuk shift ini:</p>
                            
                            {{-- Preset Buttons --}}
                            <div class="flex flex-wrap gap-2 mb-4">
                                <button type="button" @click="setWorkingDays([1,2,3,4,5])"
                                    class="px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                    Sen - Jum
                                </button>
                                <button type="button" @click="setWorkingDays([2,3,4,5,6])"
                                    class="px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                                    Sel - Sab
                                </button>
                                <button type="button" @click="setWorkingDays([0,1,2,3,4,5,6])"
                                    class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                    Semua Hari
                                </button>
                                <button type="button" @click="setWorkingDays([])"
                                    class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                    Reset
                                </button>
                            </div>

                            {{-- Day Checkboxes --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="0" 
                                        x-model="workingDays"
                                        {{ in_array(0, old('working_days', $shift->working_days ?? [])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Minggu</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="1" 
                                        x-model="workingDays"
                                        {{ in_array(1, old('working_days', $shift->working_days ?? [1,2,3,4,5])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Senin</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="2" 
                                        x-model="workingDays"
                                        {{ in_array(2, old('working_days', $shift->working_days ?? [1,2,3,4,5])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Selasa</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="3" 
                                        x-model="workingDays"
                                        {{ in_array(3, old('working_days', $shift->working_days ?? [1,2,3,4,5])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Rabu</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="4" 
                                        x-model="workingDays"
                                        {{ in_array(4, old('working_days', $shift->working_days ?? [1,2,3,4,5])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Kamis</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="5" 
                                        x-model="workingDays"
                                        {{ in_array(5, old('working_days', $shift->working_days ?? [1,2,3,4,5])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Jumat</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="working_days[]" value="6" 
                                        x-model="workingDays"
                                        {{ in_array(6, old('working_days', $shift->working_days ?? [])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Sabtu</span>
                                </label>
                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                <span x-show="workingDays.length > 0">
                                    <span x-text="workingDays.length"></span> hari dipilih
                                </span>
                                <span x-show="workingDays.length === 0" class="text-amber-600">
                                    ⚠️ Tidak ada hari kerja dipilih - akan menggunakan default (Sen-Jum)
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror"
                            placeholder="Deskripsi tambahan">{{ old('description', $shift->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                    <a href="{{ route('hris.attendance.shifts.index') }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Shift
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function shiftForm() {
                return {
                    shiftType: '{{ old('type', $shift->type) }}',
                    workMinutes: {{ old('work_hours_required', $shift->work_hours_required) }},
                    workingDays: {!! json_encode(old('working_days', $shift->working_days ?? [1,2,3,4,5])) !!},

                    formatWorkHours(minutes) {
                        if (!minutes) return '';
                        const hours = Math.floor(minutes / 60);
                        const mins = minutes % 60;
                        if (mins > 0) {
                            return `= ${hours} jam ${mins} menit`;
                        }
                        return `= ${hours} jam`;
                    },

                    setWorkingDays(days) {
                        this.workingDays = days;
                    }
                }
            }
        </script>
    @endpush
@endsection