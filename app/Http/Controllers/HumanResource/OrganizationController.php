<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCareer;
use App\Models\Level;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display the organization structure page
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $departmentId = $request->get('department_id');

        // Get all levels ordered by hierarchy (highest first)
        $levels = Level::orderBy('approval_order', 'desc')->get();

        // Get all departments with their managers
        $departments = Department::with('manager')->orderBy('name')->get();

        // Get statistics
        $stats = $this->getOrganizationStats();

        // Get org chart data with optional department filter
        $chartData = $this->buildOrgChartData($departmentId);

        // Get selected department for view
        $selectedDepartment = $departmentId ? Department::find($departmentId) : null;

        return view('admin.hris.organization.index', compact(
            'levels',
            'departments',
            'stats',
            'chartData',
            'selectedDepartment',
            'departmentId'
        ));
    }

    /**
     * Get organization chart data as JSON (for AJAX)
     */
    public function getChartData(Request $request)
    {
        $departmentId = $request->get('department_id');
        $chartData = $this->buildOrgChartData($departmentId);
        return response()->json($chartData);
    }

    /**
     * Build hierarchical org chart data based on manager relationships
     * @param int|null $departmentId Filter by department
     */
    protected function buildOrgChartData(?int $departmentId = null): array
    {
        // Build query for active employees
        $query = Employee::with(['currentCareer.department', 'currentCareer.position', 'currentCareer.level', 'currentCareer.branch', 'user'])
            ->whereHas('currentCareer', function ($q) use ($departmentId) {
                $q->where('is_active', true);
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
            })
            ->where('status', 'active');

        $employees = $query->get();

        // Build employee lookup by ID
        $employeeLookup = $employees->keyBy('id');

        // Find top-level employees (no manager or manager not in filtered set)
        $topLevel = $employees->filter(function ($emp) use ($employeeLookup) {
            $managerId = $emp->currentCareer?->manager_id;
            return !$managerId || !$employeeLookup->has($managerId);
        });

        // Build tree structure
        $tree = [];
        foreach ($topLevel as $emp) {
            $tree[] = $this->buildEmployeeNode($emp, $employeeLookup, $employees);
        }

        // Sort by level (highest first)
        usort($tree, fn($a, $b) => ($b['level_order'] ?? 0) - ($a['level_order'] ?? 0));

        return $tree;
    }

    /**
     * Build a single employee node with children
     */
    protected function buildEmployeeNode(Employee $employee, $employeeLookup, $allEmployees): array
    {
        $career = $employee->currentCareer;

        // Find direct reports (employees who have this employee as manager)
        $directReports = $allEmployees->filter(function ($emp) use ($employee) {
            return $emp->currentCareer?->manager_id === $employee->id;
        });

        $children = [];
        foreach ($directReports as $report) {
            $children[] = $this->buildEmployeeNode($report, $employeeLookup, $allEmployees);
        }

        // Sort children by level (highest first)
        usort($children, fn($a, $b) => ($b['level_order'] ?? 0) - ($a['level_order'] ?? 0));

        return [
            'id' => $employee->id,
            'name' => $employee->full_name,
            'position' => $career?->position?->name ?? '-',
            'department' => $career?->department?->name ?? '-',
            'department_code' => $career?->department?->code ?? '',
            'level' => $career?->level?->name ?? '-',
            'level_order' => $career?->level?->approval_order ?? 0,
            'branch' => $career?->branch?->name ?? '-',
            'email' => $employee->email_corporate,
            'photo' => null, // $employee->photo_url if you have photos
            'direct_reports_count' => count($children),
            'children' => $children,
        ];
    }

    /**
     * Get organization statistics
     */
    protected function getOrganizationStats(): array
    {
        $totalEmployees = Employee::where('status', 'active')->count();

        // Count employees per level via EmployeeCareer
        $levelStats = Level::orderBy('approval_order', 'desc')->get()->map(function ($level) {
            $count = EmployeeCareer::where('level_id', $level->id)
                ->where('is_current', true)
                ->where('is_active', true)
                ->count();
            return [
                'id' => $level->id,
                'name' => $level->name,
                'code' => $level->grade_code,
                'order' => $level->approval_order,
                'count' => $count,
            ];
        });

        // Employees per department
        $deptStats = Department::orderBy('name')->get()->map(function ($dept) {
            $count = EmployeeCareer::where('department_id', $dept->id)
                ->where('is_current', true)
                ->where('is_active', true)
                ->count();
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'code' => $dept->code,
                'count' => $count,
            ];
        });

        // Average span of control (employees per manager)
        $managersCount = EmployeeCareer::where('is_current', true)
            ->where('is_active', true)
            ->whereNotNull('manager_id')
            ->distinct('manager_id')
            ->count('manager_id');

        $employeesWithManager = EmployeeCareer::where('is_current', true)
            ->where('is_active', true)
            ->whereNotNull('manager_id')
            ->count();

        $avgSpanOfControl = $managersCount > 0
            ? round($employeesWithManager / $managersCount, 1)
            : 0;

        // Org depth (max levels in hierarchy)
        $maxLevel = Level::max('approval_order');
        $minLevel = Level::min('approval_order');
        $orgDepth = $maxLevel && $minLevel ? (($maxLevel - $minLevel) / 10) + 1 : 0;

        return [
            'total_employees' => $totalEmployees,
            'level_stats' => $levelStats,
            'department_stats' => $deptStats,
            'avg_span_of_control' => $avgSpanOfControl,
            'managers_count' => $managersCount,
            'org_depth' => (int) $orgDepth,
        ];
    }
}
