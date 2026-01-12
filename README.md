# 🏢 Laravel HRIS Boilerplate (Ifocabot)

<p align="center">
  <a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>
</p>

<p align="center">
  <strong>Sistem Informasi Sumber Daya Manusia (HRIS) Enterprise-Ready</strong><br>
  Built with Laravel 12, Spatie Permission, Alpine.js & TailwindCSS
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

---

## 📋 Deskripsi

Laravel HRIS Boilerplate adalah sistem manajemen SDM yang komprehensif, dirancang untuk memenuhi kebutuhan perusahaan skala menengah hingga besar. Sistem ini mencakup modul-modul lengkap mulai dari manajemen karyawan, penggajian, absensi, cuti, pelatihan, hingga portal Employee Self-Service (ESS).

---

## ✨ Fitur Utama

### 🔐 Access Control & Security
- **Role-Based Access Control (RBAC)** menggunakan Spatie Permission
- **User Management** dengan assign role
- **Permission Management** dengan granular permissions
- **Audit Logging** (Laravel Auditing) untuk tracking perubahan data
- **Dashboard Access Control** dengan statistik ringkasan

### 📢 Announcements
- Manajemen pengumuman untuk karyawan
- Target audience (semua, departemen tertentu)
- Tanggal publish & expiry

### 🏢 Master Data

#### General
- **Departments** - Struktur departemen perusahaan
- **Locations** - Cabang/lokasi dengan dukungan Geofencing

#### HRIS
- **Levels** - Jenjang jabatan dengan range gaji
- **Positions** - Posisi/jabatan per departemen

### 👥 Employee Management
- **Data Karyawan Lengkap**
  - Informasi pribadi (NIK, nama, email, telepon, alamat)
  - Foto karyawan
  - Status karyawan (active, inactive, resigned, terminated)
  - Tanggal bergabung (join date)
  
- **Sensitive Data** (terpisah untuk keamanan)
  - Nomor KTP (ID Card)
  - NPWP
  - BPJS Ketenagakerjaan
  - BPJS Kesehatan
  - Rekening bank

- **Contract Management**
  - Tipe kontrak (Permanent, Contract, Probation, Internship)
  - Periode kontrak
  - Renewal kontrak
  - Upload dokumen kontrak
  - Deactivation

- **Career History**
  - Riwayat mutasi/promosi
  - Department, Position, Level, Branch tracking
  - Gaji pokok (base salary)
  - Status aktif/non-aktif

- **Family Data**
  - Data keluarga karyawan
  - Hubungan keluarga
  - Kontak darurat
  - BPJS Dependents

- **Organization Chart**
  - Visualisasi struktur organisasi
  - Chart data hierarchy

- **Export Data** - Export karyawan ke Excel

### 💰 Payroll System

#### Payroll Components
- **Tipe Komponen**: Earning (pendapatan), Deduction (potongan)
- **Metode Kalkulasi**:
  - `fixed` - Nominal tetap
  - `percentage_of_base` - Persentase dari gaji pokok
  - `per_day` - Per hari kerja
  - `per_hour` - Per jam kerja
  - `formula` - Formula custom
  - `bpjs_jkk`, `bpjs_jkm`, `bpjs_jht_employee`, `bpjs_jht_company`
  - `bpjs_jp_employee`, `bpjs_jp_company`
  - `bpjs_kes_employee`, `bpjs_kes_company`

#### Employee Payroll Components
- Assign komponen ke karyawan
- Override amount dengan alasan
- Status aktif/non-aktif
- Effective date
- Bulk assign ke banyak karyawan

#### Employee Salary Management
- Halaman terpusat untuk kelola gaji per karyawan
- Quick view semua komponen
- Update komponen individual

#### Payroll Periods
- Periode penggajian (bulanan)
- Status: draft, processing, approved, paid
- Generate slip otomatis
- Approval workflow
- Mark as paid

#### Payroll Slips
- Slip gaji detail per karyawan
- Breakdown earnings & deductions
- Take-home pay calculation
- Download PDF
- Send via email
- Mark individual slip as paid

#### Payroll Adjustments
- Penyesuaian setelah periode terkunci
- Approval workflow
- Tipe: bonus, deduction, correction

#### Calculator Services
- **PayrollCalculator** - Kalkulasi lengkap slip gaji
- **BpjsCalculator** - Kalkulasi BPJS TK & Kesehatan
- **TaxCalculator** - Kalkulasi PPh 21
- **ComponentValidator** - Validasi komponen payroll

### ⏰ Attendance & Time Management

#### Shifts
- Konfigurasi jam kerja
- Check-in/check-out time
- Break time
- Grace period (toleransi)
- Late tolerance
- Working days per week
- Flexible shift

#### Employee Schedules
- Jadwal per karyawan per tanggal
- Assign shift
- Bulk schedule generation
- Swap shifts
- Mark as holiday/leave
- Status: scheduled, leave, holiday, sick

#### National Holidays
- Hari libur nasional
- Recurring holidays
- Copy ke tahun berikutnya

#### Attendance Logs
- Clock in/Clock out
- Location tracking (latitude, longitude)
- Leave early detection
- Late detection
- Overtime tracking
- Photo capture
- Notes

#### Attendance Summaries
- Ringkasan kehadiran harian per karyawan
- Status: present, late, absent, leave, holiday
- Actual hours worked
- Overtime hours
- Lock for payroll
- Unlock for correction

#### Overtime Management
- **Request overtime** oleh supervisor/HR
- Status: pending, approved, rejected, cancelled
- Approval workflow
- Bulk approve
- Auto-sync ke attendance summaries
- Overtime rate calculation

### 🏖️ Leave Management

#### Leave Types
- Jenis cuti (Annual, Sick, Maternity, etc.)
- Kuota per tahun
- Carry forward rules
- Required approval level
- Paid/unpaid flag

#### Employee Leave Balances
- Saldo cuti per karyawan per jenis
- Carry forward tracking

#### Leave Requests
- Pengajuan cuti
- Tanggal mulai & selesai
- Durasi (hari kerja)
- Attachment
- Approval/rejection
- Cancel request
- Admin view - semua pengajuan

### 📄 Document Management

#### Document Categories
- Kategori dokumen
- Required level (mandatory/optional)
- Max file size
- Allowed extensions
- Retention period
- Status active/inactive

#### Employee Documents
- Upload dokumen karyawan
- Expiry date tracking
- Version control
- Approval workflow
- Download
- Access logging

### 🎓 Training & Development

#### Skill Categories
- Kategori skill (Technical, Soft Skills, Leadership, etc.)

#### Skills
- Daftar skill dengan proficiency levels
- Target proficiency per posisi

#### Trainers
- Data trainer/instruktur
- Internal/external
- Specialization
- Rating

#### Training Programs
- Program pelatihan
- Status: draft, published, ongoing, completed, cancelled
- Tanggal & lokasi
- Max participants
- Prerequisites
- Required skills
- Publish, Start, Complete, Cancel actions

#### Training Courses
- Modul dalam program
- Sequence order
- Duration
- Materials (file upload)
- Reorder courses

#### Training Enrollments
- Pendaftaran peserta
- Status: pending, enrolled, in_progress, completed, cancelled
- Bulk enroll
- Approval
- Issue certificate

#### Certifications
- Master sertifikasi
- Validity period
- Issuing body

#### Employee Certifications
- Sertifikasi per karyawan
- Issue & expiry date
- Verification status
- Expiring soon alerts
- Download certificate

#### Skill Assessments
- Penilaian skill karyawan
- Assessed by (supervisor/peer/self)
- Proficiency level achieved
- Skill gap analysis
- Employee skill profile

### ✅ Approval Workflow System

#### Workflow Configuration
- Konfigurasi workflow per approval type
- Multi-level approval
- Parallel/sequential steps
- Skip conditions

#### Workflow Steps
- Step number & order
- Approver type: role, specific_user, department_head, direct_manager
- Required/optional
- Auto-approve conditions

#### Approval Requests
- Request approval untuk berbagai modul
- Polymorphic relation (approvable)
- Status tracking
- Current step

#### User Approvals
- Pending approvals dashboard
- Approval history
- Approve/reject action
- Notes/comments

### 🧑‍💼 Employee Self-Service (ESS)

#### ESS Dashboard
- Welcome page karyawan
- Announcements display
- Quick access menu

#### ESS Profile
- View profile lengkap
- Edit personal data
- Upload foto

#### ESS Leave
- Lihat saldo cuti
- Ajukan cuti
- History pengajuan
- Cancel request

#### ESS Payroll
- Lihat slip gaji
- Download slip PDF
- History slip gaji

### 🔔 Notifications
- System notifications
- Mark read/unread
- Mark all as read

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| **PHP 8.2+** | Backend runtime |
| **Laravel 12** | PHP Framework |
| **Spatie Permission** | Role & Permission management |
| **Laravel Auditing** | Audit trail logging |
| **Maatwebsite Excel** | Excel import/export |
| **Alpine.js** | Frontend interactivity |
| **TailwindCSS** | Styling |
| **Vite** | Asset bundling |
| **MySQL/PostgreSQL** | Database |

---

## 📦 Packages

```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.10.1",
    "maatwebsite/excel": "^3.1",
    "owen-it/laravel-auditing": "^14.0",
    "spatie/laravel-permission": "^6.24"
  },
  "require-dev": {
    "laravel/breeze": "^2.3",
    "pestphp/pest": "^4.2",
    "pestphp/pest-plugin-laravel": "^4.0"
  }
}
```

---

## 🚀 Instalasi

### Prerequisites
- PHP 8.2 atau lebih tinggi
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0 / PostgreSQL 14+

### Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/ifocabot/Laravel-Boilerplate-Ifocabot.git
   cd Laravel-Boilerplate-Ifocabot
   ```

2. **Install Dependencies** (One Command)
   ```bash
   composer setup
   ```
   
   Atau manual:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

3. **Configure Environment**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hris_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Seeders** (Optional - Sample Data)
   ```bash
   php artisan db:seed
   ```

5. **Start Development Server**
   ```bash
   composer dev
   ```
   Ini akan menjalankan:
   - Laravel server (`php artisan serve`)
   - Queue worker (`php artisan queue:listen`)
   - Vite dev server (`npm run dev`)

---

## 📂 Struktur Proyek

```
app/
├── Console/           # Artisan commands
├── Contracts/         # Interfaces
├── Helpers/           # Helper functions
├── Http/
│   ├── Controllers/
│   │   ├── Admin/             # Admin controllers
│   │   ├── Auth/              # Authentication
│   │   ├── ESS/               # Employee Self-Service
│   │   ├── HumanResource/     # HR modules
│   │   │   ├── Attendance/
│   │   │   ├── Leave/
│   │   │   ├── Payroll/
│   │   │   └── Training/
│   │   ├── MasterData/        # Master data controllers
│   │   └── User/              # User controllers
├── Models/            # 48 Eloquent models
├── Notifications/     # Email/notification classes
├── Observers/         # Model observers
├── Providers/         # Service providers
├── Services/          # Business logic services
│   ├── Approval/              # Approval workflow services
│   ├── Attendance/            # Attendance services
│   └── Payroll/               # Payroll calculators
├── Traits/            # Reusable traits
└── View/              # View components

database/
├── factories/         # Model factories
├── migrations/        # 72 migration files
└── seeders/           # 19 seeder classes

resources/
├── views/
│   ├── admin/         # Admin panel views
│   ├── auth/          # Authentication views
│   ├── layouts/       # Layout templates
│   └── user/          # User views
```

---

## 🔐 Default Users (After Seeding)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@example.com | password |
| HR Manager | hr@example.com | password |
| Employee | employee@example.com | password |

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Or directly
php artisan test
```

---

## 📝 API Routes Summary

| Module | Prefix | Count |
|--------|--------|-------|
| Access Control | `/access-control` | 12 routes |
| Master Data | `/master-data` | 14 routes |
| HRIS Employee | `/hris` | 25 routes |
| Payroll | `/hris/payroll` | 30 routes |
| Attendance | `/hris/attendance` | 35 routes |
| Leave | `/hris/leave` | 12 routes |
| Documents | `/hris/documents` | 14 routes |
| Training | `/hris/training` | 40 routes |
| Approvals | `/approvals` | 5 routes |
| ESS | `/ess` | 12 routes |

---

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---

## 📞 Contact

**Ifocabot Team**

- Website: [https://ifocabot.com](https://ifocabot.com)
- Repository: [https://github.com/ifocabot/Laravel-Boilerplate-Ifocabot](https://github.com/ifocabot/Laravel-Boilerplate-Ifocabot)

---

<p align="center">
  Made with ❤️ by Ifocabot Team
</p>
