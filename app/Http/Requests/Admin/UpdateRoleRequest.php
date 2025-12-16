<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ubah menjadi true atau gunakan policy/gate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique kecuali untuk role yang sedang di-update
                Rule::unique('roles', 'name')->ignore($this->route('role')),
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'exists:permissions,id',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama peran',
            'permissions' => 'izin akses',
            'permissions.*' => 'izin',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama peran wajib diisi.',
            'name.string' => 'Nama peran harus berupa teks.',
            'name.max' => 'Nama peran maksimal 255 karakter.',
            'name.unique' => 'Nama peran sudah digunakan.',
            'permissions.array' => 'Format izin tidak valid.',
            'permissions.*.exists' => 'Izin yang dipilih tidak valid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Bersihkan nama dari spasi berlebih
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        // Pastikan permissions adalah array bahkan jika kosong
        if (!$this->has('permissions')) {
            $this->merge([
                'permissions' => [],
            ]);
        }
    }
}