# Panduan Sistem Absensi - Untuk HR Admin

## Ringkasan Singkat

Sistem absensi ini mencatat kehadiran karyawan secara otomatis dan mendukung:
- Clock in/out via fingerprint atau mobile app
- Pengajuan cuti, izin, sakit
- Pengajuan lembur
- Koreksi manual oleh HR
- Audit trail lengkap

---

## Flow Absensi Harian

### 1️⃣ Persiapan (Otomatis)

**Setiap pagi sistem akan:**
- Membuat "slot" absensi untuk semua karyawan aktif
- Status awal berdasarkan jadwal:
  - **Hari kerja** → Status: *Tidak Hadir* (menunggu clock-in)
  - **Hari libur nasional** → Status: *Libur Nasional*
  - **Jadwal libur (offday)** → Status: *Hari Libur*
  - **Cuti disetujui** → Status: *Cuti*

### 2️⃣ Clock In (Karyawan)

**Saat karyawan clock in:**
1. Sistem catat waktu dan lokasi
2. Bandingkan dengan jadwal shift
3. Tentukan status:
   - **Tepat waktu** → Status: *Hadir*
   - **Terlambat** → Status: *Terlambat* + hitung menit keterlambatan

### 3️⃣ Clock Out (Karyawan)

**Saat karyawan clock out:**
1. Sistem catat waktu pulang
2. Hitung total jam kerja
3. Deteksi pulang cepat (jika ada)
4. Deteksi lembur (jika lebih dari jam kerja)

### 4️⃣ Akhir Hari (Otomatis)

**Setelah jam kerja selesai:**
- Karyawan yang tidak clock in → Status: *Alpha/Tidak Hadir*
- Sistem finalisasi perhitungan jam kerja

---

## Flow Pengajuan Cuti/Izin

```
Karyawan mengajukan cuti
        ↓
Atasan langsung review
        ↓
    [Disetujui?]
     /        \
   Ya          Tidak
   ↓            ↓
Status jadi   Tetap perlu
"Cuti"        clock in
```

**Jenis cuti yang didukung:**
| Kode | Nama | Potong Gaji? |
|------|------|--------------|
| leave | Cuti Tahunan | Tidak |
| sick | Sakit | Tidak* |
| permission | Izin | Tergantung policy |
| wfh | Work From Home | Tidak |

*Dengan surat dokter

---

## Flow Pengajuan Lembur

```
Karyawan clock out melebihi jam kerja
        ↓
Sistem deteksi lembur otomatis
        ↓
Karyawan/HR ajukan request lembur
        ↓
Atasan approve dengan jumlah jam
        ↓
Sistem update "Approved Overtime"
        ↓
Masuk perhitungan payroll
```

---

## Flow Koreksi Manual (HR Admin)

**Kapan digunakan:**
- Lupa clock in/out
- Mesin fingerprint error
- Koreksi kesalahan data

**Langkah:**
1. Buka **HRIS → Absensi → Adjustment**
2. Pilih karyawan dan tanggal
3. Masukkan koreksi (jam masuk/pulang, status, dll)
4. Isi alasan koreksi
5. Submit

> ⚠️ Semua koreksi tercatat di audit trail

---

## Status Absensi

| Status | Label | Deskripsi |
|--------|-------|-----------|
| present | Hadir | Clock in tepat waktu |
| late | Terlambat | Clock in lewat dari jadwal |
| absent | Alpha | Tidak hadir tanpa keterangan |
| leave | Cuti | Cuti tahunan disetujui |
| sick | Sakit | Izin sakit dengan surat dokter |
| permission | Izin | Izin resmi disetujui |
| holiday | Libur Nasional | Hari libur nasional |
| offday | Hari Libur | Jadwal libur mingguan |
| wfh | Work From Home | Bekerja dari rumah |

---

## Periode Payroll & Lock

**Alur periode payroll:**

```
[PENDING] → [CALCULATED] → [REVIEWED] → [LOCKED] → [PAYROLLED]
```

| Status | Boleh Edit? | Keterangan |
|--------|-------------|------------|
| PENDING | ✅ Ya | Periode berjalan |
| CALCULATED | ✅ Ya | Sudah dihitung otomatis |
| REVIEWED | ⚠️ Butuh adjustment | HR sudah review |
| LOCKED | ❌ Tidak | Sudah cutoff payroll |
| PAYROLLED | ❌ Tidak | Sudah dibayar |

---

## Menu di Sistem

### Untuk HR Admin:
- **Absensi → Rekap Harian** - Lihat absensi hari ini
- **Absensi → Summary** - Rekap per karyawan
- **Absensi → Adjustment** - Koreksi manual
- **Absensi → Audit Trail** - Riwayat perubahan
- **Cuti → Persetujuan** - Approve cuti
- **Lembur → Persetujuan** - Approve lembur

### Untuk Karyawan:
- **Clock In/Out** - Via mobile app
- **Riwayat Absensi** - Lihat rekap bulanan
- **Ajukan Cuti** - Form pengajuan cuti
- **Ajukan Lembur** - Form pengajuan lembur

---

## FAQ

**Q: Bagaimana jika karyawan lupa clock in?**
> HR bisa koreksi via menu **Adjustment** dengan mengisi alasan.

**Q: Bagaimana jika data sudah di-lock untuk payroll?**
> Data tidak bisa diubah langsung. Gunakan **Payroll Adjustment** untuk koreksi di periode berikutnya.

**Q: Bagaimana cara melihat siapa yang mengubah data?**
> Buka menu **Audit Trail** untuk melihat riwayat lengkap perubahan beserta nama user yang mengubah.

**Q: Kenapa status karyawan "Alpha" padahal sudah izin?**
> Pastikan izin sudah di-approve oleh atasan. Status otomatis berubah setelah approval.
