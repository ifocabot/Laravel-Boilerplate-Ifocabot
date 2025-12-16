<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use OwenIt\Auditing\Contracts\Auditable; // ← GANTI: harus Contracts\Auditable

class Role extends SpatieRole implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable; // ← TAMBAH: trait Auditable

    // ← HAPUS Notifiable (tidak perlu di Role)
    // ← HAPUS $fillable (sudah ada di parent SpatieRole)

    // Config auditing
    protected $auditInclude = [
        'name',
        'guard_name',
    ];

    protected $auditTimestamps = true;
}