<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $audits = Audit::with('user')
            ->latest()
            ->paginate(20);

        // Calculate statistics
        $createdCount = Audit::where('event', 'created')->count();
        $updatedCount = Audit::where('event', 'updated')->count();
        $deletedCount = Audit::where('event', 'deleted')->count();

        return view('admin.access-control.audit-logs.index', compact(
            'audits',
            'createdCount',
            'updatedCount',
            'deletedCount'
        ));
    }
}