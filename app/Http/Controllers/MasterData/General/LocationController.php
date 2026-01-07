<?php

namespace App\Http\Controllers\MasterData\General;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::with(['parent', 'children'])
            ->orderBy('name')
            ->paginate(50);

        // Calculate statistics using new flags
        $activeLocations = Location::where('is_active', true)->count();
        $parentLocations = Location::whereNull('parent_id')->count();
        $subLocations = Location::whereNotNull('parent_id')->count();
        $withGeofence = Location::where('is_geofence_enabled', true)->count();
        $stockLocations = Location::where('is_stock_location', true)->count();

        // Get type options for dropdown
        $typeOptions = Location::getTypeOptions();

        // Get parent options (locations that can have children)
        $parentOptions = Location::active()
            ->whereNotIn('type', Location::LEAF_TYPES)
            ->orderBy('name')
            ->get();

        return view('master-data.general.locations.index', compact(
            'locations',
            'activeLocations',
            'parentLocations',
            'subLocations',
            'withGeofence',
            'stockLocations',
            'typeOptions',
            'parentOptions'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                $this->validationRules(),
                $this->validationMessages()
            );

            // Validate hierarchy
            $hierarchyCheck = Location::validateHierarchy(
                $validated['parent_id'] ?? null,
                $validated['type']
            );

            if (!$hierarchyCheck['valid']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $hierarchyCheck['message']);
            }

            // Validate geofence requirements
            if (!empty($validated['is_geofence_enabled']) && $validated['is_geofence_enabled']) {
                $geofenceCheck = $this->validateGeofenceRequirements($validated);
                if (!$geofenceCheck['valid']) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', $geofenceCheck['message']);
                }
            }

            DB::beginTransaction();

            $location = Location::create($validated);

            DB::commit();

            Log::info('Location created', [
                'location_id' => $location->id,
                'name' => $location->name,
                'type' => $location->type,
            ]);

            return redirect()
                ->route('master-data.general.locations.index')
                ->with('success', 'Lokasi "' . $location->name . '" berhasil dibuat.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Location Store Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $location = Location::findOrFail($id);

            $validated = $request->validate(
                $this->validationRules($id),
                $this->validationMessages()
            );

            // Prevent circular reference
            if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
                return redirect()
                    ->back()
                    ->with('error', 'Lokasi tidak dapat menjadi induk dari dirinya sendiri.');
            }

            // Check if trying to set parent to one of its children
            if (isset($validated['parent_id']) && $validated['parent_id'] && $this->isDescendant($id, $validated['parent_id'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Lokasi tidak dapat menjadi sub dari lokasi turunannya.');
            }

            // Validate hierarchy
            $hierarchyCheck = Location::validateHierarchy(
                $validated['parent_id'] ?? null,
                $validated['type']
            );

            if (!$hierarchyCheck['valid']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $hierarchyCheck['message']);
            }

            // Check if changing type would break existing children
            if ($location->type !== $validated['type'] && $location->children()->count() > 0) {
                $typeChangeCheck = $this->validateTypeChange($location, $validated['type']);
                if (!$typeChangeCheck['valid']) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', $typeChangeCheck['message']);
                }
            }

            // Validate geofence requirements
            if (!empty($validated['is_geofence_enabled']) && $validated['is_geofence_enabled']) {
                $geofenceCheck = $this->validateGeofenceRequirements($validated);
                if (!$geofenceCheck['valid']) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', $geofenceCheck['message']);
                }
            }

            DB::beginTransaction();

            $location->update($validated);

            DB::commit();

            return redirect()
                ->route('master-data.general.locations.index')
                ->with('success', 'Lokasi "' . $location->name . '" berhasil diperbarui.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Location Update Error', [
                'error' => $e->getMessage(),
                'location_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $location = Location::findOrFail($id);

            // Check if has children
            if ($location->children()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Lokasi tidak dapat dihapus karena masih memiliki sub lokasi.');
            }

            $name = $location->name;
            $location->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Lokasi "' . $name . '" berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Location Delete Error', [
                'error' => $e->getMessage(),
                'location_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get validation rules
     */
    private function validationRules(?int $id = null): array
    {
        $typeList = implode(',', Location::TYPES);

        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code' . ($id ? ",{$id}" : ''),
            'type' => "required|in:{$typeList}",
            'parent_id' => 'nullable|exists:locations,id',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:0|max:10000',
            'is_geofence_enabled' => 'boolean',
            'is_stock_location' => 'boolean',
            'is_assignable_to_employee' => 'boolean',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get validation messages
     */
    private function validationMessages(): array
    {
        return [
            'name.required' => 'Nama lokasi wajib diisi.',
            'code.required' => 'Kode lokasi wajib diisi.',
            'code.unique' => 'Kode lokasi sudah digunakan.',
            'type.required' => 'Tipe lokasi wajib dipilih.',
            'type.in' => 'Tipe lokasi tidak valid.',
            'parent_id.exists' => 'Induk lokasi tidak valid.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'radius_meters.min' => 'Radius minimal 0 meter.',
            'radius_meters.max' => 'Radius maksimal 10000 meter.',
            'is_active.required' => 'Status lokasi wajib dipilih.',
        ];
    }

    /**
     * Validate geofence requirements when is_geofence_enabled is true
     */
    private function validateGeofenceRequirements(array $data): array
    {
        $missing = [];

        if (empty($data['latitude'])) {
            $missing[] = 'Latitude';
        }
        if (empty($data['longitude'])) {
            $missing[] = 'Longitude';
        }
        if (empty($data['radius_meters']) || $data['radius_meters'] <= 0) {
            $missing[] = 'Radius (> 0)';
        }

        if (!empty($missing)) {
            return [
                'valid' => false,
                'message' => 'Geofence diaktifkan tetapi data belum lengkap: ' . implode(', ', $missing)
            ];
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Validate if changing type would break existing children
     */
    private function validateTypeChange(Location $location, string $newType): array
    {
        $allowedChildren = Location::ALLOWED_CHILDREN[$newType] ?? [];
        $childTypes = $location->children()->pluck('type')->unique();

        $invalidChildren = $childTypes->filter(fn($type) => !in_array($type, $allowedChildren));

        if ($invalidChildren->isNotEmpty()) {
            return [
                'valid' => false,
                'message' => "Tidak dapat mengubah tipe ke '{$newType}' karena memiliki sub-lokasi dengan tipe yang tidak valid: " . $invalidChildren->implode(', ')
            ];
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Check if a location is a descendant of another
     */
    private function isDescendant($locationId, $potentialParentId): bool
    {
        $location = Location::find($potentialParentId);

        while ($location) {
            if ($location->id == $locationId) {
                return true;
            }
            $location = $location->parent;
        }

        return false;
    }

    /**
     * Check if coordinates are within radius
     */
    public function checkGeofence(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location = Location::findOrFail($validated['location_id']);

        // Check explicit geofence flag
        if (!$location->is_geofence_enabled) {
            return response()->json([
                'within_geofence' => false,
                'message' => 'Geofence tidak diaktifkan untuk lokasi ini.'
            ]);
        }

        if (!$location->hasGeofence()) {
            return response()->json([
                'within_geofence' => false,
                'message' => 'Lokasi belum memiliki data geo-fence yang lengkap.'
            ]);
        }

        $distance = $location->calculateDistance(
            $validated['latitude'],
            $validated['longitude']
        );

        $withinGeofence = $distance <= $location->radius_meters;

        return response()->json([
            'within_geofence' => $withinGeofence,
            'distance' => round($distance, 2),
            'allowed_radius' => $location->radius_meters,
            'message' => $withinGeofence
                ? 'Anda berada dalam area yang diizinkan.'
                : 'Anda berada di luar area yang diizinkan.'
        ]);
    }

    /**
     * API: Get locations for dropdown (filtered by flags)
     */
    public function getForDropdown(Request $request)
    {
        $query = Location::active();

        // Filter by flags if requested
        if ($request->boolean('geofence_only')) {
            $query->geofenceEnabled();
        }
        if ($request->boolean('stock_only')) {
            $query->stockLocations();
        }
        if ($request->boolean('assignable_only')) {
            $query->assignableToEmployee();
        }
        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        $locations = $query->orderBy('name')->get(['id', 'name', 'code', 'type']);

        return response()->json($locations);
    }
}