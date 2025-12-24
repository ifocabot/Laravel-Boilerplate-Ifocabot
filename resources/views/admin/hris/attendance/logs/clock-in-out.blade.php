@extends('layouts.admin')

@section('title', 'Clock In/Out')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6" x-data="clockInOutApp()">
        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Clock In/Out</h1>
            <p class="text-sm text-gray-500 mt-2" x-text="currentDateTime"></p>
        </div>

        {{-- Current Status Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="text-center">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full flex items-center justify-center"
                    :class="status === 'not_clocked_in' ? 'bg-red-100' : status === 'clocked_in' ? 'bg-blue-100' : 'bg-green-100'">
                    <svg class="w-12 h-12"
                        :class="status === 'not_clocked_in' ? 'text-red-600' : status === 'clocked_in' ? 'text-blue-600' : 'text-green-600'"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-2" x-text="statusLabel"></h2>

                <template x-if="todayLog && todayLog.clock_in_time">
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-600">
                            Clock In: <span class="font-semibold" x-text="todayLog.formatted_clock_in_time"></span>
                        </p>
                        <template x-if="todayLog.is_late">
                            <p class="text-sm text-orange-600">
                                Terlambat <span x-text="todayLog.late_duration_minutes"></span> menit
                            </p>
                        </template>
                        <template x-if="todayLog.clock_out_time">
                            <p class="text-sm text-gray-600">
                                Clock Out: <span class="font-semibold" x-text="todayLog.formatted_clock_out_time"></span>
                            </p>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Camera Preview --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-show="showCamera" x-cloak>
            <h3 class="text-lg font-bold text-gray-900 mb-4">Ambil Foto Selfie</h3>

            {{-- Video Preview --}}
            <div class="relative bg-gray-900 rounded-xl overflow-hidden mb-4">
                <video x-ref="video" autoplay playsinline class="w-full"></video>
                <canvas x-ref="canvas" class="hidden"></canvas>
            </div>

            {{-- Captured Photo Preview --}}
            <div x-show="capturedPhoto" class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                <img :src="capturedPhoto" class="w-full rounded-xl border border-gray-200">
            </div>

            {{-- Camera Actions --}}
            <div class="flex gap-3">
                <button @click="capturePhoto()" type="button" x-show="!capturedPhoto"
                    class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                    📸 Ambil Foto
                </button>
                <button @click="retakePhoto()" type="button" x-show="capturedPhoto"
                    class="flex-1 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-xl transition-colors">
                    🔄 Ambil Ulang
                </button>
                <button @click="closeCamera()" type="button"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    Batal
                </button>
            </div>
        </div>

        {{-- Notes Input --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-show="showCamera && capturedPhoto" x-cloak>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Catatan (Opsional)
            </label>
            <textarea x-model="notes" rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            {{-- Clock In Button --}}
            <template x-if="status === 'not_clocked_in'">
                <button @click="startClockIn()" type="button"
                    class="w-full px-8 py-4 bg-green-600 hover:bg-green-700 text-white text-lg font-bold rounded-xl transition-colors shadow-lg">
                    🕐 Clock In
                </button>
            </template>

            {{-- Clock Out Button --}}
            <template x-if="status === 'clocked_in'">
                <button @click="startClockOut()" type="button"
                    class="w-full px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold rounded-xl transition-colors shadow-lg">
                    🕐 Clock Out
                </button>
            </template>

            {{-- Already Completed --}}
            <template x-if="status === 'completed'">
                <div class="text-center py-4">
                    <p class="text-gray-600 mb-2">✅ Anda sudah menyelesaikan absensi hari ini</p>
                    <p class="text-sm text-gray-500">Sampai jumpa besok!</p>
                </div>
            </template>

            {{-- Submit Button (when photo captured) --}}
            <template x-if="showCamera && capturedPhoto">
                <button @click="submitAttendance()" type="button" :disabled="loading"
                    class="w-full mt-4 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-lg font-bold rounded-xl transition-colors shadow-lg">
                    <span x-show="!loading">✅ Submit</span>
                    <span x-show="loading">⏳ Processing...</span>
                </button>
            </template>
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
                    <span class="text-sm font-medium" :class="gpsStatus === 'active' ? 'text-green-600' : 'text-gray-600'">
                        GPS: <span x-text="gpsStatus === 'active' ? 'Aktif' : 'Tidak Aktif'"></span>
                    </span>
                </div>
                <button @click="getLocation()" type="button" class="text-xs text-indigo-600 hover:text-indigo-700">
                    Refresh
                </button>
            </div>
            <template x-if="currentLocation.latitude">
                <p class="text-xs text-gray-500 mt-2">
                    Lat: <span x-text="currentLocation.latitude.toFixed(6)"></span>,
                    Lng: <span x-text="currentLocation.longitude.toFixed(6)"></span>
                </p>
            </template>
        </div>
    </div>

    @push('scripts')
        <script>
            function clockInOutApp() {
                return {
                    employeeId: {{ auth()->user()->employee_id ?? 'null' }}, // Adjust based on your auth
                    status: 'not_clocked_in', // not_clocked_in, clocked_in, completed
                    statusLabel: 'Belum Clock In',
                    todayLog: null,
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
                    actionType: null, // 'clock_in' or 'clock_out'
                    stream: null,

                    init() {
                        this.updateDateTime();
                        setInterval(() => this.updateDateTime(), 1000);
                        this.loadTodayLog();
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

                    async loadTodayLog() {
                        // Load today's attendance log
                        // This should call an API endpoint to get today's log
                        // For now, mock data
                        this.status = 'not_clocked_in';
                        this.statusLabel = 'Belum Clock In';
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
                                    alert('Tidak dapat mengakses lokasi. Pastikan GPS aktif dan izin lokasi diberikan.');
                                }
                            );
                        } else {
                            alert('Browser tidak mendukung GPS');
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
                            alert('Tidak dapat mengakses kamera');
                            this.closeCamera();
                        }
                    },

                    capturePhoto() {
                        const video = this.$refs.video;
                        const canvas = this.$refs.canvas;

                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;

                        const context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0);

                        this.capturedPhoto = canvas.toDataURL('image/jpeg');
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
                            alert('Silakan ambil foto terlebih dahulu');
                            return;
                        }

                        if (!this.currentLocation.latitude) {
                            alert('GPS belum aktif. Silakan aktifkan GPS dan coba lagi.');
                            return;
                        }

                        this.loading = true;

                        try {
                            // Convert base64 to blob
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
                                alert(data.message);
                                this.closeCamera();
                                this.loadTodayLog();
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        } catch (error) {
                            console.error('Submit Error:', error);
                            alert('Terjadi kesalahan saat submit');
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection