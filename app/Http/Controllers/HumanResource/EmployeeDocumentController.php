<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use App\Models\DocumentCategory;
use App\Models\Employee;
use App\Models\DocumentAccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeDocument::with([
            'employee',
            'documentCategory',
            'uploadedBy',
            'approvedBy'
        ]);

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('document_category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->latest()->paginate(20);
        $employees = Employee::active()->orderBy('full_name')->get();
        $categories = DocumentCategory::active()->orderBy('name')->get();

        $stats = [
            'total' => EmployeeDocument::count(),
            'pending' => EmployeeDocument::pendingApproval()->count(),
            'approved' => EmployeeDocument::approved()->count(),
            'expired' => EmployeeDocument::expired()->count(),
        ];

        return view('admin.hris.documents.index', compact('documents', 'employees', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        $employees = Employee::active()->orderBy('full_name')->get();
        $categories = DocumentCategory::active()->orderBy('name')->get();
        $selectedEmployee = $request->employee_id ? Employee::find($request->employee_id) : null;

        return view('admin.hris.documents.create', compact('employees', 'categories', 'selectedEmployee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'document_category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'document_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:document_date',
            'document_number' => 'nullable|string|max:100',
            'issuer' => 'nullable|string|max:150',
            'is_confidential' => 'boolean',
            'notify_expiry' => 'boolean',
            'notify_days_before' => 'nullable|integer|min:1|max:365',
            'file' => 'required|file|max:' . (10 * 1024), // 10MB default
        ]);

        $category = DocumentCategory::findOrFail($validated['document_category_id']);
        $file = $request->file('file');

        // Validate file type
        $extension = $file->getClientOriginalExtension();
        if (!$category->isFileTypeAllowed($extension)) {
            throw ValidationException::withMessages([
                'file' => 'Tipe file tidak diizinkan untuk kategori ini. Diizinkan: ' . $category->allowed_file_types_string
            ]);
        }

        // Validate file size
        $fileSizeMB = $file->getSize() / 1024 / 1024;
        if ($fileSizeMB > $category->max_file_size_mb) {
            throw ValidationException::withMessages([
                'file' => "Ukuran file terlalu besar. Maksimal {$category->max_file_size_formatted}"
            ]);
        }

        try {
            DB::beginTransaction();

            // Generate unique filename
            $storedFilename = Str::uuid() . '.' . $extension;
            $filePath = "documents/employees/{$validated['employee_id']}/" . $storedFilename;

            // Store file
            Storage::put($filePath, file_get_contents($file));

            // Create document record
            $document = EmployeeDocument::create([
                ...$validated,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'file_extension' => $extension,
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
                'file_hash' => hash_file('sha256', $file->getPathname()),
                'uploaded_by' => auth()->id(),
                'status' => EmployeeDocument::STATUS_PENDING_APPROVAL,
            ]);

            // Log upload action
            DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_UPLOAD);

            DB::commit();

            return redirect()->route('hris.documents.show', $document)
                ->with('success', 'Dokumen berhasil diunggah dan menunggu persetujuan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (Storage::exists($filePath ?? '')) {
                Storage::delete($filePath);
            }
            throw ValidationException::withMessages([
                'error' => 'Gagal mengunggah dokumen: ' . $e->getMessage()
            ]);
        }
    }

    public function show(EmployeeDocument $document)
    {
        $document->load([
            'employee',
            'documentCategory',
            'uploadedBy',
            'approvedBy',
            'versions',
            'accessLogs.user'
        ]);

        // Check access permission
        if (!$document->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Log view action
        DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_VIEW);

        return view('admin.hris.documents.show', compact('document'));
    }

    public function edit(EmployeeDocument $document)
    {
        if (!$document->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $employees = Employee::active()->orderBy('full_name')->get();
        $categories = DocumentCategory::active()->orderBy('name')->get();

        return view('admin.hris.documents.edit', compact('document', 'employees', 'categories'));
    }

    public function update(Request $request, EmployeeDocument $document)
    {
        if (!$document->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'document_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:document_date',
            'document_number' => 'nullable|string|max:100',
            'issuer' => 'nullable|string|max:150',
            'is_confidential' => 'boolean',
            'notify_expiry' => 'boolean',
            'notify_days_before' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            DB::beginTransaction();

            $document->update($validated);

            // Log update action
            DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_UPDATE);

            DB::commit();

            return redirect()->route('hris.documents.show', $document)
                ->with('success', 'Dokumen berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' => 'Gagal memperbarui dokumen: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(EmployeeDocument $document)
    {
        if (!$document->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        try {
            DB::beginTransaction();

            // Log delete action
            DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_DELETE);

            $document->delete();

            DB::commit();

            return redirect()->route('hris.documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }

    public function download(EmployeeDocument $document)
    {
        if (!$document->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        if (!$document->getFileExists()) {
            return redirect()->back()
                ->with('error', 'File dokumen tidak ditemukan.');
        }

        try {
            // Log download action
            DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_DOWNLOAD);

            return Storage::download($document->file_path, $document->original_filename);

        } catch (\Exception $e) {
            // Log failed download
            DocumentAccessLog::log(
                $document,
                auth()->user(),
                DocumentAccessLog::ACTION_DOWNLOAD,
                false,
                $e->getMessage()
            );

            return redirect()->back()
                ->with('error', 'Gagal mengunduh dokumen: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, EmployeeDocument $document)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($document->status !== EmployeeDocument::STATUS_PENDING_APPROVAL) {
            return redirect()->back()
                ->with('error', 'Dokumen tidak dalam status pending approval.');
        }

        try {
            DB::beginTransaction();

            $document->approve(auth()->user(), $request->notes);

            // Log approval action
            DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_APPROVE, true, null, [], $request->notes);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Dokumen berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, EmployeeDocument $document)
    {
        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        if ($document->status !== EmployeeDocument::STATUS_PENDING_APPROVAL) {
            return redirect()->back()
                ->with('error', 'Dokumen tidak dalam status pending approval.');
        }

        try {
            DB::beginTransaction();

            $document->reject(auth()->user(), $request->notes);

            // Log rejection action
            DocumentAccessLog::log($document, auth()->user(), DocumentAccessLog::ACTION_REJECT, true, null, [], $request->notes);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Dokumen berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menolak dokumen: ' . $e->getMessage());
        }
    }
}