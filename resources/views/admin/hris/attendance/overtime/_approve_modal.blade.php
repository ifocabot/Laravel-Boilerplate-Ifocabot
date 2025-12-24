{{-- Approve Modal --}}
<div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="closeApproveModal()">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showApproveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeApproveModal()">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="showApproveModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <form @submit.prevent="approveRequest()">
                {{-- Modal Header --}}
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Approve Overtime Request</h3>
                                <p class="text-sm text-green-100">Setujui overtime request karyawan</p>
                            </div>
                        </div>
                        <button @click="closeApproveModal()" type="button"
                            class="text-white/80 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-6 space-y-4">
                    {{-- Approved Duration --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Durasi Overtime Disetujui <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" x-model="approveForm.approved_minutes" required min="0"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Menit">
                            <div class="text-sm text-gray-500">
                                = <span x-text="(approveForm.approved_minutes / 60).toFixed(2)"></span> jam
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            System menghitung: <span x-text="(approveForm.system_duration / 60).toFixed(2)"></span> jam
                        </p>
                    </div>

                    {{-- Approval Notes --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Catatan Approval (Opsional)
                        </label>
                        <textarea x-model="approveForm.notes" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Catatan dari approver..."></textarea>
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-900 mb-1">Informasi</p>
                                <p class="text-xs text-green-700">
                                    Overtime yang disetujui akan digunakan untuk kalkulasi payroll dan akan muncul di
                                    attendance summary.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                    <button @click="closeApproveModal()" type="button"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        Approve Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>