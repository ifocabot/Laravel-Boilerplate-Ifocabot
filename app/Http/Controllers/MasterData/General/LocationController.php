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

        // Calculate statistics
        $activeLocations = Location::where('is_active', true)->count();
        $parentLocations = Location::whereNull('parent_id')->count();
        $subLocations = Location::whereNotNull('parent_id')->count();
        $withGeofence = Location::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('radius_meters')
            ->count();

        return view('master-data.general.locations.index', compact(
            'locations',
            'activeLocations',
            'parentLocations',
            'subLocations',
            'withGeofence'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:locations,code',
                'type' => 'required|in:office,warehouse,store,bin', // ← Update ini
                'parent_id' => 'nullable|exists:locations,id',
                'address' => 'nullable|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'radius_meters' => 'nullable|integer|min:0|max:10000',
                'is_active' => 'required|boolean',
            ], [
                'name.required' => 'Nama lokasi wajib diisi.',
                'code.required' => 'Kode lokasi wajib diisi.',
                'code.unique' => 'Kode lokasi sudah digunakan.',
                'type.required' => 'Tipe lokasi wajib dipilih.',
                'type.in' => 'Tipe lokasi tidak valid. Pilih: office, warehouse, store, atau bin.',
                'parent_id.exists' => 'Induk lokasi tidak valid.',
                'latitude.between' => 'Latitude harus antara -90 dan 90.',
                'longitude.between' => 'Longitude harus antara -180 dan 180.',
                'radius_meters.min' => 'Radius minimal 0 meter.',
                'radius_meters.max' => 'Radius maksimal 10000 meter.',
                'is_active.required' => 'Status lokasi wajib dipilih.',
            ]);

            DB::beginTransaction();

            $location = Location::create($validated);

            DB::commit();

            Log::info('Location created', [
                'location_id' => $location->id,
                'name' => $location->name
            ]);

            return redirect()
                ->route('locations.index')
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

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:locations,code,' . $id,
                'type' => 'required|in:office,warehouse,store,bin', // ← Update ini
                'parent_id' => 'nullable|exists:locations,id',
                'address' => 'nullable|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'radius_meters' => 'nullable|integer|min:0|max:10000',
                'is_active' => 'required|boolean',
            ], [
                'name.required' => 'Nama lokasi wajib diisi.',
                'code.required' => 'Kode lokasi wajib diisi.',
                'code.unique' => 'Kode lokasi sudah digunakan.',
                'type.required' => 'Tipe lokasi wajib dipilih.',
                'type.in' => 'Tipe lokasi tidak valid. Pilih: office, warehouse, store, atau bin.',
                'parent_id.exists' => 'Induk lokasi tidak valid.',
                'latitude.between' => 'Latitude harus antara -90 dan 90.',
                'longitude.between' => 'Longitude harus antara -180 dan 180.',
                'radius_meters.min' => 'Radius minimal 0 meter.',
                'radius_meters.max' => 'Radius maksimal 10000 meter.',
                'is_active.required' => 'Status lokasi wajib dipilih.',
            ]);

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

            DB::beginTransaction();

            $location->update($validated);

            DB::commit();

            return redirect()
                ->route('locations.index')
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
     * Check if a location is a descendant of another
     */
    private function isDescendant($locationId, $potentialParentId)
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

        if (!$location->latitude || !$location->longitude || !$location->radius_meters) {
            return response()->json([
                'within_geofence' => false,
                'message' => 'Lokasi belum memiliki geo-fence.'
            ]);
        }

        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $location->latitude,
            $location->longitude
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
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }
}