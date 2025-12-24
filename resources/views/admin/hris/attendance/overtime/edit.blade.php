@extends('layouts.admin')

@section('title', 'Edit Overtime Request')

@section('content')
    <div class="space-y-6" x-data="overtimeRequestEditForm()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.overtime.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Overtime Request</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Update request overtime</p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Form Edit Request</h2>
            </div>

            <form @submit.prevent="submitForm()" class="p-6 space-y-6">
                {{-- Employee Info (Read Only) --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Karyawan</label>
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-sm font-bold text-indigo-600">
                                {{ strtoupper(substr($overtimeRequest->employee->full_name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $overtimeRequest->employee->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $overtimeRequest->employee->nik }}</p>
                        </div>
                    </div>
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Overtime <span class="text-red-500">*</span>
                    </label>
                    <input type="date" x-model="form.date" required :min="new Date().toISOString().split('T')[0]"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                {{-- Time Range --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" x-model="form.start_at" @change="calculateDuration()" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" x-model="form.end_at" @change="calculateDuration()" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                {{-- Duration Display --}}
                <div x-show="duration > 0" class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-indigo-900">Durasi Overtime</p>
                            <p class="text-2xl font-bold text-indigo-600 mt-1">
                                <span x-text="Math.floor(duration / 60)"></span> jam
                                <span x-text="duration % 60"></span> menit
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Alasan Overtime <span class="text-red-500">*</span>
                    </label>
                    <textarea x-model="form.reason" rows="3" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                </div>

                {{-- Work Description --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi Pekerjaan (Opsional)
                    </label>
                    <textarea x-model="form.work_description" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('hris.attendance.overtime.index') }}"
                        class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" :disabled="loading"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span x-show="!loading">Update Request</span>
                        <span x-show="loading">Loading...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function overtimeRequestEditForm() {
                return {
                    form: {
                        date: '{{ $overtimeRequest->date->format('Y-m-d') }}',
                        start_at: '{{ $overtimeRequest->start_at }}',
                        end_at: '{{ $overtimeRequest->end_at }}',
                        reason: '{{ $overtimeRequest->reason }}',
                        work_description: '{{ $overtimeRequest->work_description }}'
                    },
                    duration: {{ $overtimeRequest->duration_minutes }},
                    loading: false,

                    init() {
                        this.calculateDuration();
                    },

                    calculateDuration() {
                        if (!this.form.start_at || !this.form.end_at) {
                            this.duration = 0;
                            return;
                        }

                        const start = this.parseTime(this.form.start_at);
                        let end = this.parseTime(this.form.end_at);

                        if (end < start) {
                            end += 24 * 60;
                        }

                        this.duration = end - start;
                    },

                    parseTime(timeString) {
                        const [hours, minutes] = timeString.split(':').map(Number);
                        return hours * 60 + minutes;
                    },

                    async submitForm() {
                        if (this.loading) return;

                        this.loading = true;

                        try {
                            const response = await fetch('{{ route('hris.attendance.overtime.update', $overtimeRequest->id) }}', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.form)
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.showToast('success', data.message);
                                setTimeout(() => {
                                    window.location.href = '{{ route('hris.attendance.overtime.index') }}';
                                }, 1000);
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat update request');
                        } finally {
                            this.loading = false;
                        }
                    },

                    showToast(type, message) {
                        const toast = document.createElement('div');
                        toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'
                            } text-white text-sm font-medium`;
                        toast.textContent = message;
                        document.body.appendChild(toast);

                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    }
                }
            }
        </script>
    @endpush
@endsection