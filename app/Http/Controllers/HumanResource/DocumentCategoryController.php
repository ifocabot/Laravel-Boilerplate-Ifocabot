<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::with('parent', 'children')
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get();

        $stats = [
            'total' => DocumentCategory::count(),
            'active' => DocumentCategory::active()->count(),
            'required' => DocumentCategory::required()->count(),
            'confidential' => DocumentCategory::confidential()->count(),
        ];

        return view('admin.hris.documents.categories.index', compact('categories', 'stats'));
    }

    public function create()
    {
        $parentCategories = DocumentCategory::active()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.hris.documents.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:document_categories,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50|unique:document_categories,code',
            'description' => 'nullable|string',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string|in:pdf,doc,docx,jpg,jpeg,png,gif,xlsx,xls,txt',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'is_required_for_employees' => 'boolean',
            'is_confidential' => 'boolean',
            'access_roles' => 'nullable|array',
            'retention_period_months' => 'nullable|integer|min:1',
            'display_order' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = DocumentCategory::create($validated);

            DB::commit();

            return redirect()->route('hris.documents.categories.index')
                ->with('success', 'Kategori dokumen berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' => 'Gagal membuat kategori: ' . $e->getMessage()
            ]);
        }
    }

    public function show(DocumentCategory $category)
    {
        $category->load(['parent', 'children.employeeDocuments', 'employeeDocuments.employee']);

        $stats = [
            'total_documents' => $category->employeeDocuments()->count(),
            'pending_approval' => $category->employeeDocuments()->pendingApproval()->count(),
            'approved' => $category->employeeDocuments()->approved()->count(),
            'expired' => $category->employeeDocuments()->expired()->count(),
        ];

        return view('admin.hris.documents.categories.show', compact('category', 'stats'));
    }

    public function edit(DocumentCategory $category)
    {
        $parentCategories = DocumentCategory::active()
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.hris.documents.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, DocumentCategory $category)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:document_categories,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50|unique:document_categories,code,' . $category->id,
            'description' => 'nullable|string',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string|in:pdf,doc,docx,jpg,jpeg,png,gif,xlsx,xls,txt',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'is_required_for_employees' => 'boolean',
            'is_confidential' => 'boolean',
            'access_roles' => 'nullable|array',
            'retention_period_months' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category->update($validated);

            DB::commit();

            return redirect()->route('hris.documents.categories.index')
                ->with('success', 'Kategori dokumen berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' => 'Gagal memperbarui kategori: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(DocumentCategory $category)
    {
        if ($category->employeeDocuments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus kategori yang memiliki dokumen.');
        }

        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus kategori yang memiliki sub-kategori.');
        }

        try {
            $category->delete();

            return redirect()->route('hris.documents.categories.index')
                ->with('success', 'Kategori dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    public function toggleStatus(DocumentCategory $category)
    {
        $category->update([
            'is_active' => !$category->is_active
        ]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Kategori berhasil {$status}.");
    }
}
