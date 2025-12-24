<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use App\Models\Location;
use App\Models\NationalHoliday;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDepartments();
        $this->seedPositions();
        $this->seedLevels();
        $this->seedLocations();
        $this->seedNationalHolidays();

        $this->command->info('✅ Master data seeded successfully!');
    }

    /**
     * Seed departments
     */
    protected function seedDepartments(): void
    {
        $departments = [
            ['name' => 'Direksi', 'code' => 'BOD'],
            ['name' => 'Human Resource', 'code' => 'HR'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Finance & Accounting', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Sales', 'code' => 'SLS'],
            ['name' => 'Operations', 'code' => 'OPS'],
            ['name' => 'Procurement', 'code' => 'PRC'],
            ['name' => 'General Affairs', 'code' => 'GA'],
            ['name' => 'Legal', 'code' => 'LGL'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }

        $this->command->info('  📁 Departments: ' . count($departments) . ' created');
    }

    /**
     * Seed positions (no code column in schema - use name)
     */
    protected function seedPositions(): void
    {
        $positions = [
            // IT Department
            ['name' => 'Software Engineer', 'department_code' => 'IT'],
            ['name' => 'Senior Software Engineer', 'department_code' => 'IT'],
            ['name' => 'IT Manager', 'department_code' => 'IT'],
            ['name' => 'System Administrator', 'department_code' => 'IT'],
            ['name' => 'DevOps Engineer', 'department_code' => 'IT'],
            ['name' => 'QA Engineer', 'department_code' => 'IT'],

            // HR Department
            ['name' => 'HR Staff', 'department_code' => 'HR'],
            ['name' => 'HR Supervisor', 'department_code' => 'HR'],
            ['name' => 'HR Manager', 'department_code' => 'HR'],
            ['name' => 'Recruitment Specialist', 'department_code' => 'HR'],

            // Finance Department
            ['name' => 'Accountant', 'department_code' => 'FIN'],
            ['name' => 'Senior Accountant', 'department_code' => 'FIN'],
            ['name' => 'Finance Manager', 'department_code' => 'FIN'],
            ['name' => 'Tax Specialist', 'department_code' => 'FIN'],

            // Marketing Department
            ['name' => 'Marketing Staff', 'department_code' => 'MKT'],
            ['name' => 'Digital Marketing', 'department_code' => 'MKT'],
            ['name' => 'Marketing Manager', 'department_code' => 'MKT'],

            // Sales Department
            ['name' => 'Sales Representative', 'department_code' => 'SLS'],
            ['name' => 'Sales Supervisor', 'department_code' => 'SLS'],
            ['name' => 'Sales Manager', 'department_code' => 'SLS'],

            // Operations
            ['name' => 'Operations Staff', 'department_code' => 'OPS'],
            ['name' => 'Operations Supervisor', 'department_code' => 'OPS'],
            ['name' => 'Operations Manager', 'department_code' => 'OPS'],

            // General Affairs
            ['name' => 'Admin Staff', 'department_code' => 'GA'],
            ['name' => 'GA Supervisor', 'department_code' => 'GA'],
            ['name' => 'GA Manager', 'department_code' => 'GA'],

            // Executive
            ['name' => 'General Manager', 'department_code' => 'BOD'],
            ['name' => 'Director', 'department_code' => 'BOD'],
            ['name' => 'President Director', 'department_code' => 'BOD'],
        ];

        foreach ($positions as $pos) {
            $dept = Department::where('code', $pos['department_code'])->first();

            Position::firstOrCreate(
                ['name' => $pos['name']],
                [
                    'name' => $pos['name'],
                    'department_id' => $dept?->id,
                ]
            );
        }

        $this->command->info('  💼 Positions: ' . count($positions) . ' created');
    }

    /**
     * Seed levels/grades with approval hierarchy
     * Using increment of 10 for approval_order to allow future expansion
     * (e.g., Intern=5, Part-time=8 can be added below Junior Staff=10)
     */
    protected function seedLevels(): void
    {
        $levels = [
            // approval_order uses increments of 10 for future flexibility
            ['name' => 'Junior Staff', 'grade_code' => 'JR', 'approval_order' => 10, 'min_salary' => 4000000, 'max_salary' => 6000000],
            ['name' => 'Staff', 'grade_code' => 'STF', 'approval_order' => 20, 'min_salary' => 5000000, 'max_salary' => 8000000],
            ['name' => 'Senior Staff', 'grade_code' => 'SR', 'approval_order' => 30, 'min_salary' => 7000000, 'max_salary' => 12000000],
            ['name' => 'Supervisor', 'grade_code' => 'SPV', 'approval_order' => 40, 'min_salary' => 10000000, 'max_salary' => 18000000],
            ['name' => 'Manager', 'grade_code' => 'MGR', 'approval_order' => 50, 'min_salary' => 15000000, 'max_salary' => 30000000],
            ['name' => 'General Manager', 'grade_code' => 'GM', 'approval_order' => 60, 'min_salary' => 25000000, 'max_salary' => 50000000],
            ['name' => 'Director', 'grade_code' => 'DIR', 'approval_order' => 70, 'min_salary' => 40000000, 'max_salary' => 100000000],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['grade_code' => $level['grade_code']],
                $level
            );
        }

        $this->command->info('  📊 Levels: ' . count($levels) . ' created (approval_order: 10-70)');
    }

    /**
     * Seed locations/branches (only use columns in schema)
     */
    protected function seedLocations(): void
    {
        $locations = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'code' => 'JKT-HQ',
                'type' => 'office',
                'address' => 'Jl. Sudirman No. 123, Jakarta Selatan 12190',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Surabaya',
                'code' => 'SBY',
                'type' => 'office',
                'address' => 'Jl. Pemuda No. 45, Surabaya 60271',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Bandung',
                'code' => 'BDG',
                'type' => 'office',
                'address' => 'Jl. Asia Afrika No. 78, Bandung 40261',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Medan',
                'code' => 'MDN',
                'type' => 'office',
                'address' => 'Jl. Gatot Subroto No. 88, Medan 20112',
                'latitude' => 3.5952,
                'longitude' => 98.6722,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Semarang',
                'code' => 'SMG',
                'type' => 'office',
                'address' => 'Jl. Pandanaran No. 56, Semarang 50134',
                'latitude' => -6.9666,
                'longitude' => 110.4196,
                'radius_meters' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(
                ['code' => $loc['code']],
                $loc
            );
        }

        $this->command->info('  📍 Locations: ' . count($locations) . ' created');
    }

    /**
     * Seed national holidays for current year
     */
    protected function seedNationalHolidays(): void
    {
        $year = Carbon::now()->year;

        $holidays = [
            // 2024/2025 Indonesian National Holidays
            ['name' => 'Tahun Baru Masehi', 'date' => "{$year}-01-01", 'is_recurring' => true],
            ['name' => 'Isra Miraj Nabi Muhammad SAW', 'date' => "{$year}-02-08", 'is_recurring' => false],
            ['name' => 'Hari Raya Nyepi', 'date' => "{$year}-03-11", 'is_recurring' => false],
            ['name' => 'Wafat Isa Almasih', 'date' => "{$year}-03-29", 'is_recurring' => false],
            ['name' => 'Hari Raya Idul Fitri 1', 'date' => "{$year}-04-10", 'is_recurring' => false],
            ['name' => 'Hari Raya Idul Fitri 2', 'date' => "{$year}-04-11", 'is_recurring' => false],
            ['name' => 'Hari Buruh Internasional', 'date' => "{$year}-05-01", 'is_recurring' => true],
            ['name' => 'Hari Raya Waisak', 'date' => "{$year}-05-12", 'is_recurring' => false],
            ['name' => 'Kenaikan Isa Almasih', 'date' => "{$year}-05-09", 'is_recurring' => false],
            ['name' => 'Hari Lahir Pancasila', 'date' => "{$year}-06-01", 'is_recurring' => true],
            ['name' => 'Hari Raya Idul Adha', 'date' => "{$year}-06-17", 'is_recurring' => false],
            ['name' => 'Tahun Baru Islam', 'date' => "{$year}-07-07", 'is_recurring' => false],
            ['name' => 'Hari Kemerdekaan RI', 'date' => "{$year}-08-17", 'is_recurring' => true],
            ['name' => 'Maulid Nabi Muhammad SAW', 'date' => "{$year}-09-15", 'is_recurring' => false],
            ['name' => 'Hari Natal', 'date' => "{$year}-12-25", 'is_recurring' => true],
        ];

        foreach ($holidays as $holiday) {
            NationalHoliday::firstOrCreate(
                ['date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'date' => $holiday['date'],
                    'is_recurring' => $holiday['is_recurring'],
                    'description' => 'Hari Libur Nasional',
                ]
            );
        }

        $this->command->info('  🎉 National Holidays: ' . count($holidays) . ' created for ' . $year);
    }
}
