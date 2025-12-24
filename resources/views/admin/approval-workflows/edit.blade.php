@extends('layouts.admin')

@section('title', 'Edit Workflow - ' . $workflow->name)

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
                    <h1 class="text-2xl font-bold text-gray-900">Edit Workflow</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $workflow->name }}</p>
            </div>
        </div>

        <form action="{{ route('admin.approval-workflows.update', $workflow->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                                <input type="text" name="name" id="name" required
                                    value="{{ old('name', $workflow->name) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tipe <span class="text-red-500">*</span>
                                </label>
                                <select name="type" id="type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="leave" {{ old('type', $workflow->type) == 'leave' ? 'selected' : '' }}>Cuti (Leave)</option>
                                    <option value="overtime" {{ old('type', $workflow->type) == 'overtime' ? 'selected' : '' }}>Lembur (Overtime)</option>
                                    <option value="reimbursement" {{ old('type', $workflow->type) == 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                                </select>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Deskripsi
                                </label>
                                <textarea name="description" id="description" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description', $workflow->description) }}</textarea>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $workflow->is_active) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="is_active" class="text-sm text-gray-700 cursor-pointer">Workflow Aktif</label>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Step
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="bg-gray-50 rounded-xl p-4 relative">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold"
                                            x-text="index + 1"></span>
                                        <button type="button" @click="removeStep(index)" x-show="steps.length > 1"
                                            class="text-gray-400 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Approver</label>
                                            <select x-model="step.approver_type"
                                                :name="'steps[' + index + '][approver_type]'" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                                                <option value="direct_supervisor">Atasan Langsung</option>
                                                <option value="next_level_up">Level +1 (Atasan)</option>
                                                <option value="second_level_up">Level +2 (Skip-Level)</option>
                                                <option value="position_level">Berdasarkan Level</option>
                                                <option value="specific_user">User Tertentu</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai</label>
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
                                            <template x-if="step.approver_type === 'direct_supervisor' || step.approver_type === 'next_level_up' || step.approver_type === 'second_level_up'">
                                                <input type="text" disabled value="Otomatis dari struktur organisasi"
                                                    class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500">
                                            </template>
                                            <input type="hidden" :name="'steps[' + index + '][approver_value]'"
                                                x-model="step.approver_value" x-show="step.approver_type === 'direct_supervisor'">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-6 mt-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="step.can_skip_if_same"
                                                :name="'steps[' + index + '][can_skip_if_same]'" value="1"
                                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-600">Skip jika approver sama</span>
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
                        <h4 class="text-sm font-bold text-gray-900 mb-4">Preview Flow</h4>

                        <div class="space-y-3">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold"
                                        x-text="index + 1"></span>
                                    <span class="text-sm text-gray-700" x-text="getApproverLabel(step.approver_type)"></span>
                                </div>
                            </template>
                        </div>

                        <hr class="my-6">

                        <div class="space-y-3">
                            <button type="submit"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Update Workflow
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
                steps: @json($workflow->steps->map(fn($s) => [
                    'approver_type' => $s->approver_type,
                    'approver_value' => $s->approver_value ?? '',
                    'can_skip_if_same' => $s->can_skip_if_same
                ])),

                addStep() {
                    this.steps.push({
                        approver_type: 'direct_supervisor',
                        approver_value: '',
                        can_skip_if_same: true
                    });
                },

                removeStep(index) {
                    if (this.steps.length > 1) {
                        this.steps.splice(index, 1);
                    }
                },

                getApproverLabel(type) {
                    const labels = {
                        'direct_supervisor': 'Atasan Langsung',
                        'next_level_up': 'Level +1 (Atasan)',
                        'second_level_up': 'Level +2 (Skip-Level)',
                        'position_level': 'Berdasarkan Level',
                        'specific_user': 'User Tertentu'
                    };
                    return labels[type] || type;
                }
            }
        }
    </script>
    @endpush
@endsection
