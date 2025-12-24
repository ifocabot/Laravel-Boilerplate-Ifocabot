<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\OvertimeRequest;

class RetroactiveOvertimeApproval extends Notification
{
    use Queueable;

    public $overtimeRequest;
    public $daysLate;

    public function __construct(OvertimeRequest $overtimeRequest, $daysLate)
    {
        $this->overtimeRequest = $overtimeRequest;
        $this->daysLate = $daysLate;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ Retroactive Overtime Approval - Requires Attention')
            ->greeting('Hello HR Team,')
            ->line("An overtime request was approved {$this->daysLate} days late.")
            ->line("**Employee:** {$this->overtimeRequest->employee->full_name}")
            ->line("**Work Date:** {$this->overtimeRequest->formatted_date}")
            ->line("**Approved Today:** " . now()->format('Y-m-d'))
            ->line("**Approved Hours:** {$this->overtimeRequest->approved_hours} hours")
            ->warning('⚠️ Please verify if payroll for this period has been processed.')
            ->action('View Details', url('/hris/attendance/overtime/' . $this->overtimeRequest->id))
            ->line('If payroll was already processed, manual adjustment may be required.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'retroactive_overtime_approval',
            'overtime_request_id' => $this->overtimeRequest->id,
            'employee_id' => $this->overtimeRequest->employee_id,
            'employee_name' => $this->overtimeRequest->employee->full_name,
            'work_date' => $this->overtimeRequest->date->format('Y-m-d'),
            'approved_hours' => $this->overtimeRequest->approved_hours,
            'days_late' => $this->daysLate,
        ];
    }
}