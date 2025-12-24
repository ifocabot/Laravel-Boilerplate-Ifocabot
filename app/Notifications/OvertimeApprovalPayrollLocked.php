<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\OvertimeRequest;

class OvertimeApprovalPayrollLocked extends Notification
{
    use Queueable;

    public $overtimeRequest;

    public function __construct(OvertimeRequest $overtimeRequest)
    {
        $this->overtimeRequest = $overtimeRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $workDate = $this->overtimeRequest->date->format('Y-m-d');
        $approvedDate = now()->format('Y-m-d');

        return (new MailMessage)
            ->subject('🔒 Overtime Approval Blocked - Payroll Already Locked')
            ->greeting('Hello HR/Payroll Team,')
            ->line('An overtime request was approved but could not be synced to attendance summary.')
            ->line('**Reason:** Attendance summary is locked for payroll processing.')
            ->line('')
            ->line('**Employee:** ' . $this->overtimeRequest->employee->full_name)
            ->line('**Work Date:** ' . $workDate)
            ->line('**Approved Date:** ' . $approvedDate)
            ->line('**Approved Hours:** ' . $this->overtimeRequest->approved_hours . ' hours')
            ->line('**Approved Amount (minutes):** ' . $this->overtimeRequest->approved_duration_minutes)
            ->line('')
            ->error('⚠️ **ACTION REQUIRED:**')
            ->line('1. Check if payroll for this period has been processed')
            ->line('2. If NOT processed yet: Unlock summary, sync overtime, then lock again')
            ->line('3. If ALREADY processed: Add manual adjustment to next payroll')
            ->line('')
            ->action('View Overtime Request', url('/hris/attendance/overtime/' . $this->overtimeRequest->id))
            ->line('Manual adjustment may be required to ensure employee receives correct overtime pay.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'overtime_approval_payroll_locked',
            'overtime_request_id' => $this->overtimeRequest->id,
            'employee_id' => $this->overtimeRequest->employee_id,
            'employee_name' => $this->overtimeRequest->employee->full_name,
            'work_date' => $this->overtimeRequest->date->format('Y-m-d'),
            'approved_date' => now()->format('Y-m-d'),
            'approved_hours' => $this->overtimeRequest->approved_hours,
            'approved_minutes' => $this->overtimeRequest->approved_duration_minutes,
            'action_required' => 'Manual payroll adjustment or unlock summary',
        ];
    }
}