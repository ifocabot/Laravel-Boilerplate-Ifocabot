@extends('layouts.admin')

@section('title', 'Edit Penilaian Skill')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Penilaian Skill</h1>
                <p class="text-sm text-gray-500 mt-1">Ubah penilaian kompetensi karyawan</p>
            </div>
            <a href="{{ route('hris.training.assessments.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('hris.training.assessments.update', $assessment->id) }}" method="POST"
            class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Karyawan *</label>
                    <select name="employee_id" id="employee_id" required class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id', $assessment->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }} ({{ $employee->nik }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="skill_id" class="block text-sm font-medium text-gray-700 mb-2">Skill *</label>
                    <select name="skill_id" id="skill_id" required class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="">Pilih Skill</option>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" {{ old('skill_id', $assessment->skill_id) == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="assessment_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Penilaian
                        *</label>
                    <input type="date" name="assessment_date" id="assessment_date"
                        value="{{ old('assessment_date', $assessment->assessment_date->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 border rounded-xl">
                </div>
                <div>
                    <label for="assessment_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Penilaian
                        *</label>
                    <select name="assessment_type" id="assessment_type" required
                        class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="self" {{ old('assessment_type', $assessment->assessment_type) == 'self' ? 'selected' : '' }}>Self Assessment</option>
                        <option value="manager" {{ old('assessment_type', $assessment->assessment_type) == 'manager' ? 'selected' : '' }}>Manager Assessment</option>
                        <option value="peer" {{ old('assessment_type', $assessment->assessment_type) == 'peer' ? 'selected' : '' }}>Peer Review</option>
                        <option value="training" {{ old('assessment_type', $assessment->assessment_type) == 'training' ? 'selected' : '' }}>Post-Training</option>
                        <option value="certification" {{ old('assessment_type', $assessment->assessment_type) == 'certification' ? 'selected' : '' }}>Certification</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="proficiency_level" class="block text-sm font-medium text-gray-700 mb-2">Level Kemahiran
                    *</label>
                <select name="proficiency_level" id="proficiency_level" required
                    class="w-full px-4 py-2.5 border rounded-xl">
                    <option value="">Pilih Level</option>
                    <option value="1" {{ old('proficiency_level', $assessment->proficiency_level) == 1 ? 'selected' : '' }}>1
                        - Novice</option>
                    <option value="2" {{ old('proficiency_level', $assessment->proficiency_level) == 2 ? 'selected' : '' }}>2
                        - Beginner</option>
                    <option value="3" {{ old('proficiency_level', $assessment->proficiency_level) == 3 ? 'selected' : '' }}>3
                        - Competent</option>
                    <option value="4" {{ old('proficiency_level', $assessment->proficiency_level) == 4 ? 'selected' : '' }}>4
                        - Proficient</option>
                    <option value="5" {{ old('proficiency_level', $assessment->proficiency_level) == 5 ? 'selected' : '' }}>5
                        - Expert</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="score" class="block text-sm font-medium text-gray-700 mb-2">Skor (0-100)</label>
                    <input type="number" name="score" id="score" value="{{ old('score', $assessment->score) }}" min="0"
                        max="100" class="w-full px-4 py-2.5 border rounded-xl">
                </div>
                <div>
                    <label for="assessed_by" class="block text-sm font-medium text-gray-700 mb-2">Dinilai Oleh</label>
                    <select name="assessed_by" id="assessed_by" class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="">Pilih Penilai</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('assessed_by', $assessment->assessed_by) == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" id="notes" rows="3"
                    class="w-full px-4 py-2.5 border rounded-xl">{{ old('notes', $assessment->notes) }}</textarea>
            </div>
            <div class="flex justify-end gap-4">
                <a href="{{ route('hris.training.assessments.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
@endsection