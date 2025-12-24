<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * 
     * NOTE: Auto-creation of Employee is DISABLED.
     * Employee records should be created via EmployeeController which also creates User accounts.
     * This ensures proper data integrity and allows admin to set all employee details.
     */
    public function created(User $user): void
    {
        // Auto-creation disabled - Use EmployeeController to create Employee + User together
        // This observer only logs the event for debugging
        Log::debug('User created', [
            'user_id' => $user->id,
            'note' => 'Employee should be created via HRIS Employee management',
        ]);
    }

    /**
     * Handle the User "updated" event.
     * Sync name/email changes to Employee if linked
     */
    public function updated(User $user): void
    {
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            // Sync name if changed and employee name matches old name
            if ($user->isDirty('name') && $employee->full_name === $user->getOriginal('name')) {
                $employee->update(['full_name' => $user->name]);
            }

            // Sync email if changed
            if ($user->isDirty('email')) {
                $employee->update(['email_corporate' => $user->email]);
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Log the event - employee record is retained for audit purposes
        Log::info('User deleted, associated employee record retained if exists', [
            'user_id' => $user->id,
        ]);
    }
}
