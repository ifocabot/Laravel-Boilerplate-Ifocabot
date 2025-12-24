<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    protected $fillable = [
        'employee_document_id',
        'user_id',
        'action',
        'accessed_at',
        'ip_address',
        'user_agent',
        'session_id',
        'notes',
        'metadata',
        'success',
        'error_message',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'metadata' => 'array',
        'success' => 'boolean',
    ];

    const ACTION_VIEW = 'view';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_UPLOAD = 'upload';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';

    const ACTIONS = [
        self::ACTION_VIEW => 'View',
        self::ACTION_DOWNLOAD => 'Download',
        self::ACTION_UPLOAD => 'Upload',
        self::ACTION_UPDATE => 'Update',
        self::ACTION_DELETE => 'Delete',
        self::ACTION_APPROVE => 'Approve',
        self::ACTION_REJECT => 'Reject',
    ];

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public static function log(
        EmployeeDocument $document,
        User $user,
        string $action,
        bool $success = true,
        string $errorMessage = null,
        array $metadata = [],
        string $notes = null
    ): self {
        return self::create([
            'employee_document_id' => $document->id,
            'user_id' => $user->id,
            'action' => $action,
            'accessed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'notes' => $notes,
            'metadata' => $metadata,
            'success' => $success,
            'error_message' => $errorMessage,
        ]);
    }

    public function scopeForDocument($query, $documentId)
    {
        return $query->where('employee_document_id', $documentId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('accessed_at', '>=', now()->subHours($hours));
    }
}
