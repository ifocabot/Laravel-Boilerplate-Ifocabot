<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Personal Documents
            [
                'name' => 'Dokumen Pribadi',
                'code' => 'PERSONAL',
                'description' => 'Dokumen identitas dan data pribadi karyawan',
                'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_required_for_employees' => true,
                'is_confidential' => true,
                'access_roles' => ['hr-admin', 'super-admin'],
                'display_order' => 1,
                'children' => [
                    [
                        'name' => 'KTP',
                        'code' => 'KTP',
                        'description' => 'Kartu Tanda Penduduk',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 2,
                        'is_required_for_employees' => true,
                        'is_confidential' => true,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'NPWP',
                        'code' => 'NPWP',
                        'description' => 'Nomor Pokok Wajib Pajak',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 2,
                        'is_required_for_employees' => true,
                        'is_confidential' => true,
                        'display_order' => 2,
                    ],
                    [
                        'name' => 'Kartu BPJS Kesehatan',
                        'code' => 'BPJS_KES',
                        'description' => 'Kartu BPJS Kesehatan',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 2,
                        'is_required_for_employees' => false,
                        'is_confidential' => true,
                        'display_order' => 3,
                    ],
                    [
                        'name' => 'Kartu BPJS Ketenagakerjaan',
                        'code' => 'BPJS_TK',
                        'description' => 'Kartu BPJS Ketenagakerjaan',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 2,
                        'is_required_for_employees' => false,
                        'is_confidential' => true,
                        'display_order' => 4,
                    ],
                ]
            ],

            // Employment Documents
            [
                'name' => 'Dokumen Kepegawaian',
                'code' => 'EMPLOYMENT',
                'description' => 'Dokumen terkait kontrak kerja dan kepegawaian',
                'allowed_file_types' => ['pdf', 'doc', 'docx'],
                'max_file_size_mb' => 10,
                'is_required_for_employees' => true,
                'is_confidential' => false,
                'access_roles' => ['hr-admin', 'super-admin'],
                'display_order' => 2,
                'children' => [
                    [
                        'name' => 'Kontrak Kerja',
                        'code' => 'CONTRACT',
                        'description' => 'Kontrak kerja karyawan',
                        'allowed_file_types' => ['pdf', 'doc', 'docx'],
                        'max_file_size_mb' => 10,
                        'is_required_for_employees' => true,
                        'is_confidential' => false,
                        'retention_period_months' => 84, // 7 years
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Surat Penunjukan',
                        'code' => 'APPOINTMENT',
                        'description' => 'Surat penunjukan atau SK pengangkatan',
                        'allowed_file_types' => ['pdf', 'doc', 'docx'],
                        'max_file_size_mb' => 5,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 2,
                    ],
                    [
                        'name' => 'Job Description',
                        'code' => 'JOB_DESC',
                        'description' => 'Deskripsi pekerjaan',
                        'allowed_file_types' => ['pdf', 'doc', 'docx'],
                        'max_file_size_mb' => 5,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 3,
                    ],
                ]
            ],

            // Educational Documents
            [
                'name' => 'Dokumen Pendidikan',
                'code' => 'EDUCATION',
                'description' => 'Ijazah, sertifikat, dan dokumen pendidikan',
                'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_required_for_employees' => false,
                'is_confidential' => false,
                'access_roles' => ['hr-admin', 'super-admin'],
                'display_order' => 3,
                'children' => [
                    [
                        'name' => 'Ijazah Terakhir',
                        'code' => 'DIPLOMA',
                        'description' => 'Ijazah pendidikan terakhir',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 3,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Transkrip Nilai',
                        'code' => 'TRANSCRIPT',
                        'description' => 'Transkrip nilai pendidikan terakhir',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 3,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 2,
                    ],
                    [
                        'name' => 'Sertifikat Pelatihan',
                        'code' => 'CERTIFICATE',
                        'description' => 'Sertifikat pelatihan atau kursus',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 3,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 3,
                    ],
                ]
            ],

            // Medical Documents
            [
                'name' => 'Dokumen Kesehatan',
                'code' => 'MEDICAL',
                'description' => 'Surat kesehatan dan dokumen medis',
                'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_required_for_employees' => false,
                'is_confidential' => true,
                'access_roles' => ['hr-admin', 'super-admin', 'medical-staff'],
                'retention_period_months' => 60, // 5 years
                'display_order' => 4,
                'children' => [
                    [
                        'name' => 'Surat Keterangan Sehat',
                        'code' => 'HEALTH_CERT',
                        'description' => 'Surat keterangan sehat',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 3,
                        'is_required_for_employees' => false,
                        'is_confidential' => true,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Hasil Medical Check-up',
                        'code' => 'MCU',
                        'description' => 'Hasil pemeriksaan kesehatan rutin',
                        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                        'max_file_size_mb' => 5,
                        'is_required_for_employees' => false,
                        'is_confidential' => true,
                        'display_order' => 2,
                    ],
                ]
            ],

            // Other Documents
            [
                'name' => 'Dokumen Lainnya',
                'code' => 'OTHER',
                'description' => 'Dokumen lain yang relevan',
                'allowed_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'],
                'max_file_size_mb' => 10,
                'is_required_for_employees' => false,
                'is_confidential' => false,
                'access_roles' => ['hr-admin', 'super-admin'],
                'display_order' => 5,
                'children' => [
                    [
                        'name' => 'Surat Referensi',
                        'code' => 'REFERENCE',
                        'description' => 'Surat referensi dari perusahaan sebelumnya',
                        'allowed_file_types' => ['pdf', 'doc', 'docx'],
                        'max_file_size_mb' => 5,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Paklaring',
                        'code' => 'PAKLARING',
                        'description' => 'Surat pengalaman kerja',
                        'allowed_file_types' => ['pdf', 'doc', 'docx'],
                        'max_file_size_mb' => 5,
                        'is_required_for_employees' => false,
                        'is_confidential' => false,
                        'display_order' => 2,
                    ],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = DocumentCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );

            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                DocumentCategory::updateOrCreate(
                    ['code' => $childData['code']],
                    $childData
                );
            }
        }
    }
}