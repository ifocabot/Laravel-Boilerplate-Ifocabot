<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'document_category_id',
        'title',
        'description',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_extension',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'document_date',
        'expiry_date',
        'document_number',
        'issuer',
        'status',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'is_confidential',
        'access_permissions',
        'notify_expiry',
        'notify_days_before',
        'version',
        'parent_document_id',
    ];

    protected $casts = [
        'document_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'is_confidential' => 'boolean',
        'notify_expiry' => 'boolean',
        'access_permissions' => 'array',
        'file_size_bytes' => 'integer',
        'notify_days_before' => 'integer',
        'version' => 'integer',
    ];

    protected $appends = [
        'file_size_formatted',
        'status_label',
        'is_expired',
        'expires_soon',
        'download_url',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_EXPIRED => 'Expired',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'parent_document_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'parent_document_id')->orderBy('version', 'desc');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())->where('expiry_date', '!=', null);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now())
                    ->where('expiry_date', '!=', null);
    }

    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size_bytes;

        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getExpiresSoonAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        $notifyDays = $this->notify_days_before ?? 30;
        return $this->expiry_date->diffInDays(now()) <= $notifyDays && !$this->is_expired;
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    public function approve(User $approver, string $notes = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    public function reject(User $approver, string $notes = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    public function canBeAccessedBy(User $user): bool
    {
        if ($user->id === $this->employee->user_id) {
            return true;
        }

        if ($user->hasAnyRole(['super-admin', 'hr-admin'])) {
            return true;
        }

        if (!empty($this->access_permissions)) {
            $userRoles = $user->getRoleNames()->toArray();
            return !empty(array_intersect($userRoles, $this->access_permissions));
        }

        return $this->documentCategory->canUserAccess($user);
    }

    public function getFileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    public function deleteFile(): bool
    {
        if ($this->getFileExists()) {
            return Storage::delete($this->file_path);
        }
        return true;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            $document->deleteFile();
        });
    }
}
