@extends('layouts.admin')

@section('title', 'Ajukan Cuti')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ajukan Cuti</h1>
                <p class="text-sm text-gray-500 mt-1">Buat pengajuan cuti baru</p>
            </div>
            <a href="{{ route('ess.leave.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="lg:col-span-2">
                <form action="{{ route('ess.leave.store') }}" method="POST"
                    class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
                    @csrf
                    <div>
                        <label for="leave_type_id" class="block text-sm font-medium text-gray-700 mb-2">Jenis Cuti *</label>
                        <select name="leave_type_id" id="leave_type_id" required
                            class="w-full px-4 py-2.5 border rounded-xl">
                            <option value="">Pilih Jenis Cuti</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                    @if(isset($leaveBalances[$type->id]))
                                        (Sisa: {{ $leaveBalances[$type->id]->remaining }} hari)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai
                                *</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border rounded-xl">
                            @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai
                                *</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border rounded-xl">
                            @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                        <textarea name="reason" id="reason" rows="4" required class="w-full px-4 py-2.5 border rounded-xl"
                            placeholder="Jelaskan alasan pengajuan cuti Anda...">{{ old('reason') }}</textarea>
                        @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-4 pt-4 border-t">
                        <a href="{{ route('ess.leave.index') }}"
                            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Kirim
                            Pengajuan</button>
                    </div>
                </form>
            </div>

            {{-- Leave Balance Sidebar --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Saldo Cuti Anda</h3>
                <div class="space-y-4">
                    @foreach($leaveBalances as $balance)
                        <div class="flex justify-between items-center py-2 border-b last:border-0">
                            <span class="text-sm text-gray-600">{{ $balance->leaveType->name }}</span>
                            <span class="font-bold text-indigo-600">{{ $balance->remaining }} hari</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection