<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SkillCategory;
use App\Models\Skill;
use App\Models\Trainer;
use App\Models\Certification;
use App\Models\Employee;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Training & Development data...');

        // Skill Categories
        $categories = [
            ['code' => 'TECH', 'name' => 'Technical Skills', 'description' => 'Kemampuan teknis dan IT'],
            ['code' => 'SOFT', 'name' => 'Soft Skills', 'description' => 'Kemampuan interpersonal dan komunikasi'],
            ['code' => 'MGMT', 'name' => 'Management', 'description' => 'Kemampuan manajerial dan kepemimpinan'],
            ['code' => 'LANG', 'name' => 'Language', 'description' => 'Kemampuan bahasa'],
        ];

        foreach ($categories as $cat) {
            SkillCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
        $this->command->info('  ✅ Skill categories seeded');

        // Skills
        $skills = [
            ['code' => 'PHP', 'name' => 'PHP Programming', 'skill_category_id' => 1],
            ['code' => 'JS', 'name' => 'JavaScript', 'skill_category_id' => 1],
            ['code' => 'SQL', 'name' => 'Database SQL', 'skill_category_id' => 1],
            ['code' => 'COMM', 'name' => 'Communication', 'skill_category_id' => 2],
            ['code' => 'TEAM', 'name' => 'Teamwork', 'skill_category_id' => 2],
            ['code' => 'PROB', 'name' => 'Problem Solving', 'skill_category_id' => 2],
            ['code' => 'LEAD', 'name' => 'Leadership', 'skill_category_id' => 3],
            ['code' => 'PROJ', 'name' => 'Project Management', 'skill_category_id' => 3],
            ['code' => 'ENG', 'name' => 'English', 'skill_category_id' => 4],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['code' => $skill['code']], $skill);
        }
        $this->command->info('  ✅ Skills seeded');

        // Certifications
        $certifications = [
            ['code' => 'AWS-SAA', 'name' => 'AWS Solutions Architect', 'issuing_organization' => 'Amazon Web Services', 'level' => 'intermediate', 'validity_months' => 36],
            ['code' => 'PMP', 'name' => 'Project Management Professional', 'issuing_organization' => 'PMI', 'level' => 'advanced', 'validity_months' => 36],
            ['code' => 'SCRUM', 'name' => 'Certified ScrumMaster', 'issuing_organization' => 'Scrum Alliance', 'level' => 'intermediate', 'validity_months' => 24],
            ['code' => 'TOEFL', 'name' => 'TOEFL iBT', 'issuing_organization' => 'ETS', 'level' => 'beginner', 'validity_months' => 24],
        ];

        foreach ($certifications as $cert) {
            Certification::firstOrCreate(['code' => $cert['code']], $cert);
        }
        $this->command->info('  ✅ Certifications seeded');

        // External Trainer
        Trainer::firstOrCreate(
            ['email' => 'trainer@external.com'],
            [
                'type' => 'external',
                'name' => 'John External Trainer',
                'email' => 'trainer@external.com',
                'phone' => '08123456789',
                'organization' => 'Training Academy',
                'expertise' => 'Leadership, Communication, Team Building',
                'is_active' => true,
            ]
        );
        $this->command->info('  ✅ External trainer seeded');

        $this->command->info('✨ Training & Development data seeded successfully!');
    }
}
