<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;

class ApprovalWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Leave Approval Workflow
        $leaveWorkflow = ApprovalWorkflow::create([
            'name' => 'Approval Cuti',
            'type' => 'leave',
            'description' => 'Workflow untuk pengajuan cuti karyawan',
            'is_active' => true,
        ]);

        ApprovalWorkflowStep::create([
            'workflow_id' => $leaveWorkflow->id,
            'step_order' => 1,
            'approver_type' => 'direct_supervisor',
            'is_required' => true,
            'can_skip_if_same' => true,
        ]);

        // Overtime Approval Workflow
        $overtimeWorkflow = ApprovalWorkflow::create([
            'name' => 'Approval Lembur',
            'type' => 'overtime',
            'description' => 'Workflow untuk pengajuan lembur karyawan',
            'is_active' => true,
        ]);

        ApprovalWorkflowStep::create([
            'workflow_id' => $overtimeWorkflow->id,
            'step_order' => 1,
            'approver_type' => 'direct_supervisor',
            'is_required' => true,
            'can_skip_if_same' => true,
        ]);

        // Reimbursement Approval Workflow
        $reimbursementWorkflow = ApprovalWorkflow::create([
            'name' => 'Approval Reimbursement',
            'type' => 'reimbursement',
            'description' => 'Workflow untuk pengajuan reimbursement',
            'is_active' => true,
        ]);

        ApprovalWorkflowStep::create([
            'workflow_id' => $reimbursementWorkflow->id,
            'step_order' => 1,
            'approver_type' => 'direct_supervisor',
            'is_required' => true,
            'can_skip_if_same' => true,
        ]);

        $this->command->info('Default approval workflows created successfully.');
    }
}
