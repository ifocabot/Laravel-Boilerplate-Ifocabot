# 📖 Tutorial Payroll - Panduan Lengkap

Dokumentasi lengkap proses payroll dari awal hingga slip gaji.

---

## 📋 Daftar Isi

1. [Persiapan Awal](#1-persiapan-awal)
2. [Setup Komponen Gaji](#2-setup-komponen-gaji)
3. [Assign Gaji ke Karyawan](#3-assign-gaji-ke-karyawan)
4. [Setup Shift & Jadwal](#4-setup-shift--jadwal)
5. [Recording Kehadiran](#5-recording-kehadiran)
6. [Overtime Management](#6-overtime-management)
7. [Generate Payroll](#7-generate-payroll)
8. [Review & Approval](#8-review--approval)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Persiapan Awal

### Checklist Sebelum Mulai

- [ ] Data karyawan sudah lengkap (NIK, nama, status pajak)
- [ ] Komponen gaji sudah di-setup
- [ ] Shift kerja sudah dibuat
- [ ] Jadwal karyawan sudah di-assign

### Struktur Data

```
Employee
├── EmployeeSensitiveData (NPWP, status pajak, bank)
├── EmployeeCareer (jabatan, department)
├── EmployeePayrollComponent (gaji pokok, tunjangan)
├── EmployeeSchedule (jadwal shift)
└── AttendanceSummary (ringkasan kehadiran)
```

---

## 2. Setup Komponen Gaji

### Lokasi Menu
`Payroll & Salary → Payroll → Salary Components`

### Jenis Komponen

| Type | Deskripsi | Contoh |
|------|-----------|--------|
| `earning` | Penghasilan (+) | Gaji Pokok, Tunjangan |
| `deduction` | Potongan (-) | BPJS, PPh21, Pinjaman |

### Komponen Wajib

#### A. Penghasilan (Earning)

| Kode | Nama | Taxable | Formula |
|------|------|---------|---------|
| `GAPOK` | Gaji Pokok | ✅ Ya | Fixed amount |
| `TJ_TRANSPORT` | Tunjangan Transport | ✅ Ya | Fixed/per hari masuk |
| `TJ_MAKAN` | Tunjangan Makan | ✅ Ya | Per hari masuk |
| `TJ_JABATAN` | Tunjangan Jabatan | ✅ Ya | Fixed per bulan |
| `LEMBUR` | Uang Lembur | ✅ Ya | Per jam OT approved |

#### B. Potongan (Deduction)

| Kode | Nama | Keterangan |
|------|------|------------|
| `BPJS_KES` | BPJS Kesehatan | 1% dari gaji |
| `BPJS_TK` | BPJS Ketenagakerjaan | 2% dari gaji |
| `PPH21` | Pajak PPh 21 | Dihitung otomatis |
| `POT_ALPHA` | Potongan Alpha | Per hari alpha |
| `POT_TELAT` | Potongan Telat | Per menit telat |

### Cara Membuat Komponen

1. Klik **"+ Tambah Komponen"**
2. Isi form:
   - **Kode**: Unik, uppercase (e.g., `GAPOK`)
   - **Nama**: Nama lengkap
   - **Tipe**: Earning / Deduction
   - **Taxable**: Apakah kena pajak
   - **Is Active**: Aktifkan
3. **Simpan**

---

## 3. Assign Gaji ke Karyawan

### Lokasi Menu
`Payroll & Salary → Payroll → Employee Salaries`

### Langkah-langkah

1. Klik nama karyawan
2. Klik **"Assign Component"**
3. Pilih komponen gaji
4. Masukkan nominal:
   - **Gaji Pokok**: e.g., Rp 5.000.000
   - **Tunjangan**: sesuai kebijakan
5. Pilih **Effective Date**
6. **Simpan**

### Contoh Setup

```
Budi Santoso (Staff)
├── Gaji Pokok      : Rp 5.000.000
├── Tj. Transport   : Rp 500.000
├── Tj. Makan       : Rp 25.000/hari
├── BPJS Kesehatan  : 1% × Gaji Pokok
└── BPJS TK         : 2% × Gaji Pokok
```

---

## 4. Setup Shift & Jadwal

### A. Buat Shift
`Attendance & Time → Attendance → Shifts Management`

| Field | Contoh |
|-------|--------|
| Nama | Office Regular |
| Start Time | 08:00 |
| End Time | 17:00 |
| Break Minutes | 60 |
| Late Tolerance | 15 menit |

### B. Assign Jadwal
`Attendance & Time → Attendance → Employee Schedules`

1. Pilih karyawan
2. Pilih periode (bulan)
3. Pilih shift
4. Klik **"Generate"** atau assign manual per tanggal

---

## 5. Recording Kehadiran

### Sumber Data Kehadiran

| Sumber | Deskripsi |
|--------|-----------|
| Clock In/Out | Dari mesin fingerprint atau app |
| Manual Entry | Input manual oleh HR |
| Leave Request | Pengajuan cuti yang approved |
| Overtime Request | Lembur yang approved |

### Status Kehadiran

| Status | Deskripsi | Impact Gaji |
|--------|-----------|-------------|
| `present` | Hadir normal | Full pay |
| `late` | Hadir tapi telat | Pay - potongan telat |
| `alpha` | Tidak hadir tanpa alasan | Zero pay + potongan |
| `leave` | Cuti | Sesuai policy cuti |
| `sick` | Sakit (dengan surat) | Full pay |
| `offday` | Hari libur/weekend | N/A |
| `holiday` | Libur nasional | Full pay |

### View Ringkasan Kehadiran
`Attendance & Time → Attendance → Attendance Summary`

---

## 6. Overtime Management

### Lokasi Menu
`Attendance & Time → Overtime → All Requests`

### Flow Overtime

```
Employee Request → Manager Approval → HR Validation → Payroll
```

### Tarif Lembur (Default)

| Jam Ke- | Rate |
|---------|------|
| 1 | 1.5× per jam |
| 2+ | 2× per jam |
| Hari Libur | 2× per jam |

### Formula

```
Tarif per jam = Gaji Pokok / 173
Lembur Jam 1 = Tarif × 1.5
Lembur Jam 2+ = Tarif × 2.0
```

---

## 7. Generate Payroll

### Step-by-Step

#### Step 1: Buat Period
`Payroll & Salary → Payroll → Payroll Periods`

1. Klik **"+ Buat Periode"**
2. Isi:
   - Tahun & Bulan
   - Tanggal Mulai - Selesai
   - Tanggal Pembayaran
3. **Simpan**

#### Step 2: Review Kehadiran
- Pastikan semua attendance summary sudah ter-record
- Check overtime yang approved
- Fix data yang salah sebelum generate

#### Step 3: Generate Slips
1. Buka detail periode
2. Klik **"Generate Slips"**
3. Sistem akan menghitung:
   - Gaji pokok × hari kerja
   - Tunjangan
   - Overtime approved
   - Potongan (BPJS, PPh21, alpha, telat)
4. Wait until complete

#### Step 4: Review Slips
- Check setiap slip
- Bandingkan dengan expected
- Edit jika ada kesalahan

---

## 8. Review & Approval

### Workflow

```
Draft → Review → Approved → Paid → Closed
```

### Approve Period
1. Review semua slip
2. Klik **"Approve"**
3. Period status = `approved`

### Mark as Paid
1. Setelah transfer bank
2. Klik **"Mark as Paid"**
3. Semua slip ter-update

### Lock Attendance (Penting!)
- Sebelum approve, **lock attendance**
- Perubahan setelah lock → masuk sebagai **Adjustment**
- Adjustment diproses di periode berikutnya

---

## 9. Troubleshooting

### Problem: Slip tidak ter-generate

**Penyebab:**
- Karyawan tidak punya komponen gaji
- Status karyawan bukan `active`

**Solusi:**
```bash
# Check employee salary components
php artisan tinker
>>> Employee::find(1)->payrollComponents
```

### Problem: Overtime tidak terhitung

**Penyebab:**
- OT belum approved
- OT di luar periode

**Solusi:**
- Approve OT request dulu
- Check tanggal OT vs periode

### Problem: Potongan alpha tidak muncul

**Penyebab:**
- Attendance summary belum di-generate
- Tidak ada komponen `POT_ALPHA`

**Solusi:**
```bash
php artisan attendance:mark-alpha --date=2026-01-15
```

---

## 🧪 Testing dengan Dummy Data

### Seed Test Data
```bash
# Default scenario
php artisan payroll:seed-test

# Custom: banyak telat
php artisan payroll:seed-test --present=10 --late=8 --alpha=3

# Bulan tertentu
php artisan payroll:seed-test --year=2026 --month=1 --clean
```

### Full Test Flow
```bash
# 1. Seed data
php artisan payroll:seed-test --month=1 --year=2026

# 2. Generate period summaries (optional)
php artisan payroll:generate-summaries 1

# 3. Buka browser
# 4. Go to /hris/payroll/periods
# 5. Click "Generate Slips"
# 6. Review!
```

---

## 📊 Contoh Perhitungan

### Karyawan: Budi Santoso

**Data:**
- Gaji Pokok: Rp 5.000.000
- Status Pajak: TK/0
- Hari Kerja Normal: 22 hari
- Hadir: 18 hari
- Telat: 2 hari (total 45 menit)
- Alpha: 1 hari
- Cuti: 1 hari
- Overtime Approved: 10 jam

**Perhitungan:**

```
PENGHASILAN
├── Gaji Pokok (prorate)    : 5.000.000 × (18/22) = 4.090.909
├── Tunjangan Transport     : 500.000
├── Tunjangan Makan         : 25.000 × 18 = 450.000
├── Lembur                  : (5.000.000/173) × 10 × 1.5 = 433.526
└── TOTAL PENGHASILAN       : 5.474.435

POTONGAN
├── BPJS Kesehatan (1%)     : 50.000
├── BPJS TK (2%)            : 100.000
├── PPh 21                  : 0 (di bawah PTKP)
├── Potongan Alpha (1 hari) : 5.000.000 / 22 = 227.273
├── Potongan Telat          : 45 × 1.000 = 45.000
└── TOTAL POTONGAN          : 422.273

GAJI BERSIH                 : 5.474.435 - 422.273 = 5.052.162
```

---

## 🔗 Quick Links

| Menu | Path |
|------|------|
| Salary Components | `/hris/payroll/components` |
| Employee Salaries | `/hris/payroll/employee-salaries` |
| Payroll Periods | `/hris/payroll/periods` |
| Attendance Summary | `/hris/attendance/summaries` |
| Overtime Requests | `/hris/attendance/overtime` |
| Adjustments | `/hris/payroll/adjustments` |

---

*Dokumentasi ini dibuat untuk LaraHRIS v1.0*
