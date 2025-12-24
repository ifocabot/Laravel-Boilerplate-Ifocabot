@extends('layouts.admin')

@section('title', 'Request Overtime Baru')

@section('content')
    <div class="space-y-6" x-data="overtimeRequestForm()">
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
                    <h1 class="text-2xl font-bold text-gray-900">Request Overtime Baru</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Buat request overtime untuk karyawan</p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Form Request Overtime</h2>
            </div>

            <form @submit.prevent="submitForm()" class="p-6 space-y-6">
                {{-- Employee Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Karyawan <span class="text-red-500">*</span>
                    </label>
                    <select x-model="form.employee_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Overtime <span class="text-red-500">*</span>
                    </label>
                    <input type="date" x-model="form.date" required :min="new Date().toISOString().split('T')[0]"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="mt-2 text-xs text-gray-500">Tanggal harus hari ini atau setelahnya</p>
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
                            <p class="text-xs text-indigo-700 mt-1">
                                = <span x-text="(duration / 60).toFixed(2)"></span> jam
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
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Misal: Deploy production urgent, deadline project client, dll"></textarea>
                    <p class="mt-2 text-xs text-gray-500">Jelaskan alasan mengapa perlu overtime</p>
                </div>

                {{-- Work Description --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi Pekerjaan (Opsional)
                    </label>
                    <textarea x-model="form.work_description" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Detail pekerjaan yang akan dilakukan selama overtime"></textarea>
                    <p class="mt-2 text-xs text-gray-500">Jelaskan detail pekerjaan yang akan dilakukan (opsional)</p>
                </div>

                {{-- Info Box --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-900 mb-1">Informasi</p>
                            <ul class="text-xs text-blue-700 space-y-1">
                                <li>• Request overtime akan diajukan untuk approval manager</li>
                                <li>• Overtime hanya akan dibayar jika sudah disetujui</li>
                                <li>• Pastikan waktu overtime sesuai dengan kebutuhan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('hris.attendance.overtime.index') }}"
                        class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" :disabled="loading"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span x-show="!loading">Kirim Request</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function overtimeRequestForm() {
                return {
                    form: {
                        employee_id: '',
                        date: '',
                        start_at: '',
                        end_at: '',
                        reason: '',
                        work_description: ''
                    },
                    duration: 0,
                    loading: false,

                    calculateDuration() {
                        if (!this.form.start_at || !this.form.end_at) {
                            this.duration = 0;
                            return;
                        }

                        const start = this.parseTime(this.form.start_at);
                        let end = this.parseTime(this.form.end_at);

                        // Handle overnight
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

                        if (!this.form.employee_id || !this.form.date || !this.form.start_at || !this.form.end_at || !this.form.reason) {
                            alert('Mohon lengkapi semua field yang wajib diisi');
                            return;
                        }

                        if (this.duration <= 0) {
                            alert('Durasi overtime tidak valid. Pastikan jam selesai lebih besar dari jam mulai');
                            return;
                        }

                        this.loading = true;

                        try {
                            const response = await fetch('{{ route('hris.attendance.overtime.store') }}', {
                                method: 'POST',
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
                                if (data.errors) {
                                    const errorMessages = Object.values(data.errors).flat().join('\n');
                                    alert('Validasi gagal:\n' + errorMessages);
                                } else {
                                    alert(data.message || 'Terjadi kesalahan');
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengirim request');
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