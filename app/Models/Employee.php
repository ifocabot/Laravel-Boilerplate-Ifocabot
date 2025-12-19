<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'nik',
        'full_name',
        'email_corporate',
        'phone_number',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'marital_status',
        'religion',
        'join_date',
        'resign_date',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'resign_date' => 'date',
    ];

    /**
     * Boot method - Auto create sensitive data
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($employee) {
            EmployeeSensitiveData::create([
                'employee_id' => $employee->id,
            ]);
        });
    }

    /**
     * ========================================
     * RELATIONSHIPS - BASIC
     * ========================================
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sensitiveData(): HasOne
    {
        return $this->hasOne(EmployeeSensitiveData::class);
    }

    /**
     * ========================================
     * RELATIONSHIPS - CAREER
     * ========================================
     */

    /**
     * All career records
     */
    public function careers(): HasMany
    {
        return $this->hasMany(EmployeeCareer::class);
    }

    /**
     * All career history records (alias, sorted)
     */
    public function careerHistories(): HasMany
    {
        return $this->hasMany(EmployeeCareer::class)
            ->orderBy('start_date', 'desc');
    }

    /**
     * Active careers only
     */
    public function activeCareers(): HasMany
    {
        return $this->hasMany(EmployeeCareer::class)
            ->where('is_active', true);
    }

    /**
     * Current/latest active career (using is_current flag if exists, or latest if not)
     */
    public function currentCareer(): HasOne
    {
        return $this->hasOne(EmployeeCareer::class)
            ->where('is_current', true)
            ->latest('start_date');
    }

    /**
     * ========================================
     * RELATIONSHIPS - CONTRACT
     * ========================================
     */

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function activeContracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class)
            ->where('is_active', true);
    }

    /**
     * ========================================
     * RELATIONSHIPS - FAMILY
     * ========================================
     */

    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class)
            ->where('is_emergency_contact', true);
    }

    public function bpjsDependents(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class)
            ->where('is_bpjs_dependent', true);
    }

    /**
     * ========================================
     * RELATIONSHIPS - PAYROLL
     * ========================================
     */

    public function payrollComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }

    public function activePayrollComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class)
            ->where('is_active', true)
            ->with('component');
    }

    public function payrollSlips(): HasMany
    {
        return $this->hasMany(PayrollSlip::class);
    }

    /**
     * ========================================
     * ACCESSORS - CAREER RELATED
     * ========================================
     */

    /**
     * Get current department
     */
    public function getCurrentDepartmentAttribute()
    {
        return $this->currentCareer?->department;
    }

    /**
     * Get current position
     */
    public function getCurrentPositionAttribute()
    {
        return $this->currentCareer?->position;
    }

    /**
     * Get current level
     */
    public function getCurrentLevelAttribute()
    {
        return $this->currentCareer?->level;
    }

    /**
     * Get current branch
     */
    public function getCurrentBranchAttribute()
    {
        return $this->currentCareer?->branch;
    }

    /**
     * Get current manager
     */
    public function getManagerAttribute()
    {
        return $this->currentCareer?->manager;
    }

    /**
     * Get career history (sorted with relationships)
     */
    public function getCareerHistoryAttribute()
    {
        return $this->careers()
            ->orderBy('start_date', 'desc')
            ->with(['department', 'position', 'level', 'branch', 'manager'])
            ->get();
    }

    /**
     * ========================================
     * ACCESSORS - CONTRACT RELATED
     * ========================================
     */

    public function getCurrentContractAttribute()
    {
        return $this->contracts()
            ->where('is_active', true)
            ->first();
    }

    public function getContractHistoryAttribute()
    {
        return $this->contracts()
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function getContractTypeAttribute()
    {
        return $this->current_contract?->type;
    }

    public function getContractTypeLabelAttribute()
    {
        return $this->current_contract?->type_label;
    }

    /**
     * ========================================
     * ACCESSORS - SENSITIVE DATA (Shortcuts)
     * ========================================
     */

    public function getIdCardNumberAttribute()
    {
        return $this->sensitiveData?->id_card_number;
    }

    public function getMaskedIdCardNumberAttribute()
    {
        return $this->sensitiveData?->masked_id_card_number;
    }

    public function getNpwpNumberAttribute()
    {
        return $this->sensitiveData?->npwp_number;
    }

    public function getMaskedNpwpNumberAttribute()
    {
        return $this->sensitiveData?->masked_npwp_number;
    }

    public function getBpjsTkNumberAttribute()
    {
        return $this->sensitiveData?->bpjs_tk_number;
    }

    public function getBpjsKesNumberAttribute()
    {
        return $this->sensitiveData?->bpjs_kes_number;
    }

    public function getBankAccountNumberAttribute()
    {
        return $this->sensitiveData?->bank_account_number;
    }

    public function getMaskedBankAccountNumberAttribute()
    {
        return $this->sensitiveData?->masked_bank_account_number;
    }

    public function getTaxStatusAttribute()
    {
        return $this->sensitiveData?->tax_status;
    }

    public function getEmergencyContactAttribute()
    {
        return [
            'name' => $this->sensitiveData?->emergency_contact_name,
            'relationship' => $this->sensitiveData?->emergency_contact_relationship,
            'phone' => $this->sensitiveData?->emergency_contact_phone,
            'address' => $this->sensitiveData?->emergency_contact_address,
        ];
    }

    /**
     * ========================================
     * ACCESSORS - PAYROLL
     * ========================================
     */

    public function getLatestPayrollSlipAttribute()
    {
        return $this->payrollSlips()
            ->latest('slip_date')
            ->first();
    }

    public function getCurrentBasicSalaryAttribute()
    {
        $basicSalaryComponent = $this->activePayrollComponents()
            ->whereHas('component', function ($q) {
                $q->where('code', 'BASIC_SALARY');
            })
            ->first();

        return $basicSalaryComponent ? $basicSalaryComponent->amount : 0;
    }

    public function getFormattedCurrentBasicSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->current_basic_salary, 0, ',', '.');
    }

    /**
     * ========================================
     * ACCESSORS - PERSONAL INFO
     * ========================================
     */

    public function getAgeAttribute()
    {
        return $this->date_of_birth
            ? $this->date_of_birth->age
            : null;
    }

    public function getTenureAttribute()
    {
        if (!$this->join_date)
            return 0;

        $endDate = $this->resign_date ?? now();
        return $this->join_date->diffInYears($endDate);
    }

    public function getFormattedTenureAttribute(): string
    {
        if (!$this->join_date)
            return '0 tahun';

        $endDate = $this->resign_date ?? now();
        $years = $this->join_date->diffInYears($endDate);
        $months = $this->join_date->copy()->addYears($years)->diffInMonths($endDate);

        $result = [];
        if ($years > 0)
            $result[] = "{$years} tahun";
        if ($months > 0)
            $result[] = "{$months} bulan";

        return !empty($result) ? implode(' ', $result) : '0 bulan';
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    public function resign(Carbon $date, string $reason = null): void
    {
        $this->update([
            'status' => 'resigned',
            'resign_date' => $date,
        ]);

        $this->careers()->where('is_active', true)->update([
            'is_active' => false,
            'end_date' => $date,
            'notes' => $reason ?? 'Employee resigned',
        ]);

        $this->contracts()->where('is_active', true)->update([
            'is_active' => false,
            'end_date' => $date,
            'notes' => $reason ?? 'Employee resigned',
        ]);
    }

    public function terminate(Carbon $date, string $reason = null): void
    {
        $this->update([
            'status' => 'terminated',
            'resign_date' => $date,
        ]);

        $this->careers()->where('is_active', true)->update([
            'is_active' => false,
            'end_date' => $date,
            'notes' => $reason ?? 'Employee terminated',
        ]);

        $this->contracts()->where('is_active', true)->update([
            'is_active' => false,
            'end_date' => $date,
            'notes' => $reason ?? 'Employee terminated',
        ]);
    }

    public function reactivate(): void
    {
        $this->update([
            'status' => 'active',
            'resign_date' => null,
        ]);
    }

    public static function generateNik(): string
    {
        $year = date('y');
        $month = date('m');

        $lastEmployee = self::whereYear('join_date', date('Y'))
            ->whereMonth('join_date', date('m'))
            ->latest('id')
            ->first();

        $sequence = $lastEmployee ? (int) substr($lastEmployee->nik, -4) + 1 : 1;

        return $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('careers', function ($q) use ($departmentId) {
            $q->where('is_active', true)
                ->where('department_id', $departmentId);
        });
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->whereHas('careers', function ($q) use ($positionId) {
            $q->where('is_active', true)
                ->where('position_id', $positionId);
        });
    }

    /**
     * ========================================
     * RELATIONSHIPS - ATTENDANCE
     * ========================================
     */

    /**
     * Employee schedules/roster
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /**
     * Get current month schedules
     */
    public function currentMonthSchedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->orderBy('date');
    }

    /**
     * Get schedule for today
     */
    public function getTodayScheduleAttribute()
    {
        return $this->schedules()
            ->where('date', today())
            ->first();
    }

    /**
     * Check if employee is scheduled today
     */
    public function getIsScheduledTodayAttribute(): bool
    {
        $schedule = $this->today_schedule;
        return $schedule && $schedule->is_working_day;
    }

    /**
     * Get current shift for today
     */
    public function getTodayShiftAttribute()
    {
        return $this->today_schedule?->shift;
    }
}