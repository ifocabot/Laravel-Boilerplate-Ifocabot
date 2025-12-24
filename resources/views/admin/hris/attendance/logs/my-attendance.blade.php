@extends('layouts.admin')

@section('title', 'My Attendance')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="myAttendanceApp()">
        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Absensi Saya</h1>
            <p class="text-sm text-gray-500 mt-2" x-text="currentDateTime"></p>
        </div>

        {{-- Employee Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-xl font-bold text-indigo-600">
                        {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                    </span>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900">{{ $employee->full_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $employee->nik }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $employee->currentCareer?->position?->name ?? '-' }} •
                        {{ $employee->currentCareer?->department?->name ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Today's Schedule --}}
        <div class="{{ $schedule->schedule_type_badge_class ?? 'bg-gray-100' }} rounded-xl shadow-lg p-6">
            <h3 class="text-sm font-semibold mb-3">Status Hari Ini</h3>
            <div class="flex items-center justify-between">
                <div>
                    {{-- Menggunakan Accessor schedule_type_label --}}
                    <p class="text-2xl font-bold">{{ $schedule->schedule_type_label ?? 'Tidak ada jam kerja' }}</p>

                    @if($schedule->shift ?? false)
                        <p class="text-sm opacity-90 mt-1">{{ $schedule->shift->time_range ?? 'Tidak ada jam kerja' }}</p>
                    @else
                        <p class="text-sm opacity-90 mt-1">{{ $schedule->notes ?? 'Tidak ada jam kerja' }}</p>
                    @endif
                </div>

                @if($schedule->shift ?? false)
                    <div class="text-right">
                        <p class="text-sm opacity-90">Durasi Kerja</p>
                        <p class="text-2xl font-bold">{{ $schedule->shift->work_hours_required }} menit</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Current Status Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="text-center">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full flex items-center justify-center"
                    :class="currentStatus.color + '-bg'">
                    <svg class="w-12 h-12" :class="currentStatus.color + '-text'" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-2" x-text="currentStatus.label"></h2>

                @if($todayLog)
                    <div class="mt-4 space-y-2">
                        @if($todayLog->has_clocked_in)
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm text-gray-700">
                                    Clock In: <span class="font-semibold">{{ $todayLog->formatted_clock_in_time }}</span>
                                </span>
                            </div>

                            @if($todayLog->is_late)
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-lg">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span class="text-sm text-orange-700 font-semibold">
                                        Terlambat {{ $todayLog->late_duration_minutes }} menit
                                    </span>
                                </div>
                            @endif
                        @endif

                        @if($todayLog->has_clocked_out)
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm text-gray-700">
                                    Clock Out: <span class="font-semibold">{{ $todayLog->formatted_clock_out_time }}</span>
                                </span>
                            </div>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">
                                    Durasi Kerja: <span class="font-semibold">{{ $todayLog->formatted_work_duration }}</span>
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Camera Preview Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-show="showCamera" x-cloak x-transition>
            <h3 class="text-lg font-bold text-gray-900 mb-4">📸 Ambil Foto Selfie</h3>

            {{-- Video Preview --}}
            <div class="relative bg-gray-900 rounded-xl overflow-hidden mb-4" x-show="!capturedPhoto">
                <video x-ref="video" autoplay playsinline class="w-full max-h-96 object-cover"></video>
            </div>

            {{-- Captured Photo Preview --}}
            <div x-show="capturedPhoto" class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Preview Foto:</p>
                <img :src="capturedPhoto" class="w-full max-h-96 object-cover rounded-xl border-2 border-gray-200">
            </div>

            {{-- Camera Controls --}}
            <div class="flex gap-3 mb-4">
                <button @click="capturePhoto()" type="button" x-show="!capturedPhoto"
                    class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors shadow-sm">
                    📸 Ambil Foto
                </button>
                <button @click="retakePhoto()" type="button" x-show="capturedPhoto"
                    class="flex-1 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-xl transition-colors shadow-sm">
                    🔄 Ambil Ulang
                </button>
                <button @click="closeCamera()" type="button"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                    ✕ Batal
                </button>
            </div>

            {{-- Notes Input --}}
            <div x-show="capturedPhoto">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea x-model="notes" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>

            {{-- Submit Button --}}
            <div x-show="capturedPhoto" class="mt-4">
                <button @click="submitAttendance()" type="button" :disabled="loading"
                    class="w-full px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white text-lg font-bold rounded-xl transition-all shadow-lg">
                    <span x-show="!loading"
                        x-text="'✅ Submit ' + (actionType === 'clock_in' ? 'Clock In' : 'Clock Out')"></span>
                    <span x-show="loading">⏳ Processing...</span>
                </button>
            </div>
        </div>

        {{-- Action Buttons (Main) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-show="!showCamera">
            @if($schedule)
                <template x-if="currentStatus.type === 'not_clocked_in'">
                    <button @click="startClockIn()" type="button"
                        class="w-full px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-lg font-bold rounded-xl transition-all shadow-lg">
                        🕐 Clock In Sekarang
                    </button>
                </template>

                <template x-if="currentStatus.type === 'clocked_in'">
                    <button @click="startClockOut()" type="button"
                        class="w-full px-8 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-lg font-bold rounded-xl transition-all shadow-lg">
                        🕐 Clock Out Sekarang
                    </button>
                </template>

                <template x-if="currentStatus.type === 'completed'">
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-700 font-semibold mb-2">✅ Absensi Hari Ini Selesai</p>
                        <p class="text-sm text-gray-500">Terima kasih atas kerja keras Anda!</p>
                        <p class="text-xs text-gray-400 mt-2">Sampai jumpa besok 👋</p>
                    </div>
                </template>
            @endif
        </div>

        {{-- GPS Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" :class="gpsStatus === 'active' ? 'text-green-600' : 'text-gray-400'" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div>
                        <span class="text-sm font-medium"
                            :class="gpsStatus === 'active' ? 'text-green-600' : 'text-gray-600'">
                            GPS: <span x-text="gpsStatus === 'active' ? 'Aktif' : 'Tidak Aktif'"></span>
                        </span>
                        <template x-if="currentLocation.latitude">
                            <p class="text-xs text-gray-500">
                                <span x-text="currentLocation.latitude.toFixed(6)"></span>,
                                <span x-text="currentLocation.longitude.toFixed(6)"></span>
                            </p>
                        </template>
                    </div>
                </div>
                <button @click="getLocation()" type="button"
                    class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors font-medium">
                    🔄 Refresh
                </button>
            </div>
        </div>

        {{-- Recent Logs --}}
        @if($recentLogs->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Riwayat 7 Hari Terakhir</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($recentLogs as $log)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $log->formatted_date }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $log->shift?->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-4">
                                        @if($log->has_clocked_in)
                                            <div>
                                                <p class="text-xs text-gray-500">Clock In</p>
                                                <p class="text-sm font-semibold text-gray-900">{{ $log->formatted_clock_in_time }}</p>
                                            </div>
                                        @endif
                                        @if($log->has_clocked_out)
                                            <div>
                                                <p class="text-xs text-gray-500">Clock Out</p>
                                                <p class="text-sm font-semibold text-gray-900">{{ $log->formatted_clock_out_time }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    @if($log->is_late)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700 mt-1">
                                            Terlambat
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function myAttendanceApp() {
                return {
                    employeeId: {{ $employee->id }},
                    currentStatus: {
                        type: '{{ $todayLog ? ($todayLog->has_clocked_out ? "completed" : "clocked_in") : "not_clocked_in" }}',
                        label: '{{ $todayLog ? ($todayLog->has_clocked_out ? "Selesai" : "Sedang Bekerja") : "Belum Clock In" }}',
                        color: '{{ $todayLog ? ($todayLog->has_clocked_out ? "bg-green-100 text-green-600" : "bg-blue-100 text-blue-600") : "bg-red-100 text-red-600" }}'
                    },
                    currentDateTime: '',
                    showCamera: false,
                    capturedPhoto: null,
                    notes: '',
                    currentLocation: {
                        latitude: null,
                        longitude: null
                    },
                    gpsStatus: 'inactive',
                    loading: false,
                    actionType: null,
                    stream: null,

                    init() {
                        this.updateDateTime();
                        setInterval(() => this.updateDateTime(), 1000);
                        this.getLocation();
                    },

                    updateDateTime() {
                        const now = new Date();
                        const options = {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        };
                        this.currentDateTime = now.toLocaleDateString('id-ID', options);
                    },

                    getLocation() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.currentLocation = {
                                        latitude: position.coords.latitude,
                                        longitude: position.coords.longitude
                                    };
                                    this.gpsStatus = 'active';
                                },
                                (error) => {
                                    console.error('GPS Error:', error);
                                    this.gpsStatus = 'inactive';

                                    // Testing Mode: Gunakan koordinat dummy
                                    const useDummy = confirm('⚠️ Tidak dapat mengakses lokasi.\n\n🧪 Gunakan lokasi dummy untuk testing?\n(Klik OK untuk testing mode)');

                                    if (useDummy) {
                                        // Dummy coordinates (Jakarta area)
                                        this.currentLocation = {
                                            latitude: -6.200000,
                                            longitude: 106.816666
                                        };
                                        this.gpsStatus = 'active';
                                        alert('✅ Menggunakan koordinat dummy: Jakarta, Indonesia');
                                    }
                                }
                            );
                        } else {
                            alert('❌ Browser tidak mendukung GPS');
                        }
                    },

                    async startClockIn() {
                        this.actionType = 'clock_in';
                        await this.openCamera();
                    },

                    async startClockOut() {
                        this.actionType = 'clock_out';
                        await this.openCamera();
                    },

                    async openCamera() {
                        this.showCamera = true;
                        this.getLocation();

                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: 'user' }
                            });
                            this.$refs.video.srcObject = this.stream;
                        } catch (error) {
                            console.error('Camera Error:', error);

                            // Testing Mode: Gunakan foto dummy jika camera tidak tersedia
                            const useDummy = confirm('⚠️ Kamera tidak tersedia.\n\n🧪 Gunakan foto dummy untuk testing?\n(Klik OK untuk testing mode)');

                            if (useDummy) {
                                // Generate dummy image (gray rectangle with text)
                                this.generateDummyPhoto();
                            } else {
                                alert('❌ Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
                                this.closeCamera();
                            }
                        }
                    },

                    generateDummyPhoto() {
                        const canvas = document.createElement('canvas');
                        canvas.width = 640;
                        canvas.height = 480;

                        const ctx = canvas.getContext('2d');

                        // Background
                        ctx.fillStyle = '#e5e7eb';
                        ctx.fillRect(0, 0, 640, 480);

                        // Border
                        ctx.strokeStyle = '#6366f1';
                        ctx.lineWidth = 4;
                        ctx.strokeRect(10, 10, 620, 460);

                        // Text
                        ctx.fillStyle = '#4b5563';
                        ctx.font = 'bold 24px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('🧪 TESTING MODE', 320, 220);
                        ctx.font = '18px Arial';
                        ctx.fillText('Dummy Photo for Development', 320, 250);
                        ctx.font = '14px Arial';
                        ctx.fillText(new Date().toLocaleString('id-ID'), 320, 280);

                        this.capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);

                        // Hide video preview since we're using dummy
                        if (this.$refs.video) {
                            this.$refs.video.style.display = 'none';
                        }
                    },

                    capturePhoto() {
                        const video = this.$refs.video;
                        const canvas = document.createElement('canvas');

                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;

                        const context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0);

                        this.capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
                    },

                    retakePhoto() {
                        this.capturedPhoto = null;
                    },

                    closeCamera() {
                        if (this.stream) {
                            this.stream.getTracks().forEach(track => track.stop());
                        }
                        this.showCamera = false;
                        this.capturedPhoto = null;
                        this.notes = '';
                    },

                    async submitAttendance() {
                        if (!this.capturedPhoto) {
                            alert('❌ Silakan ambil foto terlebih dahulu');
                            return;
                        }

                        if (!this.currentLocation.latitude) {
                            alert('❌ GPS belum aktif. Silakan aktifkan GPS dan coba lagi.');
                            return;
                        }

                        this.loading = true;

                        try {
                            const blob = await fetch(this.capturedPhoto).then(r => r.blob());

                            const formData = new FormData();
                            formData.append('employee_id', this.employeeId);
                            formData.append('latitude', this.currentLocation.latitude);
                            formData.append('longitude', this.currentLocation.longitude);
                            formData.append('photo', blob, 'photo.jpg');
                            formData.append('notes', this.notes);

                            const endpoint = this.actionType === 'clock_in'
                                ? '{{ route("hris.attendance.logs.clock-in") }}'
                                : '{{ route("hris.attendance.logs.clock-out") }}';

                            const response = await fetch(endpoint, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                alert('✅ ' + data.message);
                                window.location.reload();
                            } else {
                                alert('❌ ' + (data.message || 'Terjadi kesalahan'));
                            }
                        } catch (error) {
                            console.error('Submit Error:', error);
                            alert('❌ Terjadi kesalahan saat submit');
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection