@extends('layouts.admin')

@section('title', 'Tambah Workflow')

@section('content')
    <div class="space-y-6" x-data="workflowForm()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('admin.approval-workflows.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Tambah Workflow</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Buat workflow approval baru dengan langkah-langkah persetujuan</p>
            </div>
        </div>

        <form action="{{ route('admin.approval-workflows.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Basic Info --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Dasar</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Workflow <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Contoh: Approval Cuti">
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tipe <span class="text-red-500">*</span>
                                </label>
                                <select name="type" id="type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Pilih Tipe</option>
                                    <option value="leave" {{ old('type') == 'leave' ? 'selected' : '' }}>Cuti (Leave)</option>
                                    <option value="overtime" {{ old('type') == 'overtime' ? 'selected' : '' }}>Lembur
                                        (Overtime)</option>
                                    <option value="reimbursement" {{ old('type') == 'reimbursement' ? 'selected' : '' }}>
                                        Reimbursement</option>
                                </select>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Deskripsi
                                </label>
                                <textarea name="description" id="description" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Deskripsi workflow (opsional)">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Approval Steps --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Langkah Persetujuan</h3>
                            <button type="button" @click="addStep()"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Step
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="bg-gray-50 rounded-xl p-4 relative">
                                    <div class="flex items-start justify-between mb-3">
                                        <span
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold"
                                            x-text="index + 1"></span>
                                        <button type="button" @click="removeStep(index)" x-show="steps.length > 1"
                                            class="text-gray-400 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe
                                                Approver</label>
                                            <select x-model="step.approver_type"
                                                :name="'steps[' + index + '][approver_type]'" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                                <optgroup label="Berdasarkan Struktur">
                                                    <option value="direct_supervisor">Atasan Langsung</option>
                                                    <option value="department_head">Kepala Departemen</option>
                                                    <option value="relative_level">Level Relatif (+N)</option>
                                                </optgroup>
                                                <optgroup label="Berdasarkan Role">
                                                    <option value="role">Role Tertentu</option>
                                                    <option value="specific_user">User Tertentu</option>
                                                </optgroup>
                                                <optgroup label="Procurement/Finance">
                                                    <option value="cost_center_owner">Owner Cost Center</option>
                                                </optgroup>
                                                <optgroup label="Legacy (HR)">
                                                    <option value="next_level_up">Level +1 (Atasan)</option>
                                                    <option value="second_level_up">Level +2 (Skip-Level)</option>
                                                    <option value="position_level">Berdasarkan Level ID</option>
                                                </optgroup>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">Nilai/Parameter</label>
                                            {{-- Relative Level --}}
                                            <template x-if="step.approver_type === 'relative_level'">
                                                <select x-model="step.approver_value"
                                                    :name="'steps[' + index + '][approver_value]'"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                                    <option value="+1">+1 Level (Atasan)</option>
                                                    <option value="+2">+2 Level (Skip-Level)</option>
                                                    <option value="+3">+3 Level</option>
                                                </select>
                                            </template>
                                            {{-- Role --}}
                                            <template x-if="step.approver_type === 'role'">
                                                <input type="text" x-model="step.approver_value"
                                                    :name="'steps[' + index + '][approver_value]'"
                                                    placeholder="Nama role (contoh: hr_manager)"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                            </template>
                                            {{-- Position Level --}}
                                            <template x-if="step.approver_type === 'position_level'">
                                                <select x-model="step.approver_value"
                                                    :name="'steps[' + index + '][approver_value]'"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                                    <option value="">Pilih Level</option>
                                                    @foreach($levels as $level)
                                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                                    @endforeach
                                                </select>
                                            </template>
                                            {{-- Specific User --}}
                                            <template x-if="step.approver_type === 'specific_user'">
                                                <select x-model="step.approver_value"
                                                    :name="'steps[' + index + '][approver_value]'"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                                    <option value="">Pilih User</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </template>
                                            {{-- Auto types --}}
                                            <template
                                                x-if="['direct_supervisor', 'department_head', 'cost_center_owner', 'next_level_up', 'second_level_up'].includes(step.approver_type)">
                                                <input type="text" disabled value="Otomatis dari struktur organisasi"
                                                    class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500">
                                            </template>
                                            <input type="hidden" :name="'steps[' + index + '][approver_value]'"
                                                x-model="step.approver_value"
                                                x-show="['direct_supervisor', 'department_head', 'cost_center_owner'].includes(step.approver_type)">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Jika Gagal
                                                Resolve</label>
                                            <select x-model="step.on_resolution_fail"
                                                :name="'steps[' + index + '][on_resolution_fail]'"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                                <option value="fail_request">Gagalkan Request</option>
                                                <option value="skip_step">Skip Step Ini</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" x-model="step.can_skip_if_same"
                                                    :name="'steps[' + index + '][can_skip_if_same]'" value="1"
                                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-600">Skip jika approver sama</span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Failure Message --}}
                                    <div class="mt-3" x-show="step.on_resolution_fail === 'fail_request'">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Pesan Gagal <span class="text-gray-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" x-model="step.failure_message"
                                            :name="'steps[' + index + '][failure_message]'"
                                            placeholder="Contoh: Tidak ada approver yang tersedia"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                    </div>

                                    {{-- Conditions Section --}}
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Conditions <span class="text-gray-400 font-normal">(Opsional)</span>
                                            </label>
                                            <button type="button" @click="addCondition(index)"
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded transition-colors">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                Tambah Kondisi
                                            </button>
                                        </div>

                                        <template x-if="step.conditions && step.conditions.length > 0">
                                            <div class="space-y-2">
                                                <template x-for="(condition, cIndex) in step.conditions" :key="cIndex">
                                                    <div
                                                        class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200">
                                                        <select x-model="condition.field"
                                                            class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500">
                                                            <option value="">Pilih Field</option>
                                                            <option value="amount">Amount (Rp)</option>
                                                            <option value="days_requested">Jumlah Hari</option>
                                                            <option value="department_id">Department ID</option>
                                                            <option value="requester_level">Level Requester</option>
                                                            <option value="leave_type_id">Tipe Cuti</option>
                                                        </select>
                                                        <select x-model="condition.operator"
                                                            class="w-20 px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500">
                                                            <option value="=">=</option>
                                                            <option value="!=">≠</option>
                                                            <option value=">">&gt;</option>
                                                            <option value="<">&lt;</option>
                                                            <option value=">=">&ge;</option>
                                                            <option value="<=">&le;</option>
                                                        </select>
                                                        <input type="text" x-model="condition.value" placeholder="Nilai"
                                                            class="w-24 px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500">
                                                        <button type="button" @click="removeCondition(index, cIndex)"
                                                            class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="!step.conditions || step.conditions.length === 0">
                                            <p class="text-xs text-gray-400 italic">Tidak ada kondisi - step akan selalu
                                                aktif</p>
                                        </template>

                                        {{-- Hidden input for conditions JSON --}}
                                        <input type="hidden" :name="'steps[' + index + '][conditions]'"
                                            :value="JSON.stringify(step.conditions || [])">
                                    </div>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-gray-500 mt-3">
                            * Langkah approval akan dijalankan secara berurutan dari step 1 hingga step terakhir
                        </p>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
                        <h4 class="text-sm font-bold text-gray-900 mb-4">Preview Flow</h4>

                        <div class="space-y-3">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold"
                                        x-text="index + 1"></span>
                                    <span class="text-sm text-gray-700"
                                        x-text="getApproverLabel(step.approver_type)"></span>
                                </div>
                            </template>
                        </div>

                        <hr class="my-6">

                        <div class="space-y-3">
                            <button type="submit"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Simpan Workflow
                            </button>
                            <a href="{{ route('admin.approval-workflows.index') }}"
                                class="w-full block text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function workflowForm() {
                return {
                    steps: [
                        { approver_type: 'direct_supervisor', approver_value: '', can_skip_if_same: true, on_resolution_fail: 'fail_request', conditions: [] }
                    ],

                    addStep() {
                        this.steps.push({
                            approver_type: 'direct_supervisor',
                            approver_value: '',
                            can_skip_if_same: true,
                            on_resolution_fail: 'fail_request',
                            failure_message: '',
                            conditions: []
                        });
                    },

                    removeStep(index) {
                        if (this.steps.length > 1) {
                            this.steps.splice(index, 1);
                        }
                    },

                    addCondition(stepIndex) {
                        if (!this.steps[stepIndex].conditions) {
                            this.steps[stepIndex].conditions = [];
                        }
                        this.steps[stepIndex].conditions.push({
                            field: '',
                            operator: '=',
                            value: ''
                        });
                    },

                    removeCondition(stepIndex, conditionIndex) {
                        this.steps[stepIndex].conditions.splice(conditionIndex, 1);
                    },

                    getApproverLabel(type) {
                        const labels = {
                            'direct_supervisor': 'Atasan Langsung',
                            'department_head': 'Kepala Departemen',
                            'relative_level': 'Level Relatif',
                            'role': 'Role Tertentu',
                            'specific_user': 'User Tertentu',
                            'cost_center_owner': 'Owner Cost Center',
                            'next_level_up': 'Level +1 (Atasan)',
                            'second_level_up': 'Level +2 (Skip-Level)',
                            'position_level': 'Berdasarkan Level'
                        };
                        return labels[type] || type;
                    }
                }
            }
        </script>
    @endpush
@endsection