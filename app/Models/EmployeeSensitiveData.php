<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EmployeeSensitiveData extends Model
{
    protected $table = 'employee_sensitive_data';

    protected $fillable = [
        'employee_id',
        'id_card_number',
        'npwp_number',
        'bpjs_tk_number',
        'bpjs_kes_number',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'tax_status',
        'tax_calculation_method',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_address',
    ];

    /**
     * Encrypted attributes using Laravel's built-in encryption
     */
    protected $casts = [
        'id_card_number' => 'encrypted',
        'npwp_number' => 'encrypted',
        'bpjs_tk_number' => 'encrypted',
        'bpjs_kes_number' => 'encrypted',
        'bank_account_number' => 'encrypted',
        'emergency_contact_phone' => 'encrypted',
    ];

    /**
     * Relationship
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Accessor - Masked ID Card Number (show last 4 digits)
     */
    protected function maskedIdCardNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->id_card_number
            ? '****-****-****-' . substr($this->id_card_number, -4)
            : null
        );
    }

    /**
     * Accessor - Masked NPWP Number
     */
    protected function maskedNpwpNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->npwp_number
            ? '**.***.***.**-***-' . substr($this->npwp_number, -3)
            : null
        );
    }

    /**
     * Accessor - Masked Bank Account
     */
    protected function maskedBankAccountNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bank_account_number
            ? '****-****-' . substr($this->bank_account_number, -4)
            : null
        );
    }

    /**
     * Accessor - Masked Emergency Phone
     */
    protected function maskedEmergencyContactPhone(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->emergency_contact_phone
            ? '****-****-' . substr($this->emergency_contact_phone, -4)
            : null
        );
    }
}