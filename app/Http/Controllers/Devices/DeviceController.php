<?php

namespace App\Http\Controllers\Devices;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sort', 'direction', 'per_page']);

        $query = Device::with('users')
            ->withCount(['hydroponic_setups as active_setups_count' => function ($q) {
                $q->where('is_archived', false)->where('status', 'active');
            }])
            ->where('is_archived', false);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('device_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('serial_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('users', function ($userQuery) use ($filters) {
                      $userQuery->where('first_name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        // Apply status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $query->orderBy($sortField, $sortDirection);

        // Get per_page value from request, default to 10, allow only [10, 25, 50, 100]
        $perPage = in_array($filters['per_page'] ?? 10, [10, 25, 50, 100]) 
            ? ($filters['per_page'] ?? 10) 
            : 10;
        
        $devices = $query->paginate($perPage);
        $filteredCount = $devices->total();

        // Only query total count if filters are applied, otherwise use filtered count
        $hasFilters = !empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all');
        $deviceCount = $hasFilters
            ? Device::where('is_archived', false)->count()
            : $filteredCount;

        $users = User::where('role', 'user')
            ->where('is_archived', false)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        return Inertia::render('devices/index', [
            'devices' => $devices,
            'deviceCount' => $deviceCount,
            'filteredCount' => $filteredCount,
            'users' => $users,
            'filters' => $filters,
        ]);
    }

      public function archived(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sort', 'direction', 'per_page']);

        $query = Device::with('users')
            ->where('is_archived', true);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('device_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('serial_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('users', function ($userQuery) use ($filters) {
                      $userQuery->where('first_name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        // Apply status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $query->orderBy($sortField, $sortDirection);

        // Get per_page value from request, default to 10, allow only [10, 25, 50, 100]
        $perPage = in_array($filters['per_page'] ?? 10, [10, 25, 50, 100]) 
            ? ($filters['per_page'] ?? 10) 
            : 10;
        
        $devices = $query->paginate($perPage);
        $filteredArchivedCount = $devices->total();

        // Only query total count if filters are applied, otherwise use filtered count
        $hasFilters = !empty($filters['search']);
        $archivedCount = $hasFilters
            ? Device::where('is_archived', true)->count()
            : $filteredArchivedCount;

        return Inertia::render('devices/archive-devices', [
            'devices' => $devices,
            'archivedCount' => $archivedCount,
            'filteredArchivedCount' => $filteredArchivedCount,
            'filters' => $filters,
        ]);
    }

    /**
     * Archive a device.
     * Business rules: device must be offline and have no active hydroponic setups.
     */
    public function archive(string $id)
    {
        try {
            $device = Device::with('hydroponic_setups')->findOrFail($id);

            if ($device->is_archived) {
                return redirect()->back()->withErrors(['error' => 'Device is already archived.']);
            }

            if ($device->status === 'online') {
                throw ValidationException::withMessages([
                    'error' => 'Cannot archive an online device. Take the device offline first.',
                ]);
            }

            $activeSetupsCount = $device->hydroponic_setups()
                ->where('is_archived', false)
                ->where('status', 'active')
                ->count();

            if ($activeSetupsCount > 0) {
                throw ValidationException::withMessages([
                    'error' => 'Cannot archive device with active hydroponic setups. Archive or deactivate the setups first.',
                ]);
            }

            $device->update(['is_archived' => true]);

            return redirect()->back()->with('success', 'Device archived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to archive device.']);
        }
    }

    /**
     * Bulk archive devices
     */
    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:devices,id',
        ]);

        // Find devices that meet archive conditions
        $eligibleDevices = Device::withCount(['hydroponic_setups as active_setups_count' => function ($q) {
                $q->where('is_archived', false)->where('status', 'active');
            }])
            ->whereIn('id', $validated['ids'])
            ->where('is_archived', false)
            ->where('status', 'offline')
            ->get()
            ->filter(function ($device) {
                return $device->active_setups_count == 0;
            });

        if ($eligibleDevices->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'No devices meet the archive requirements (must be offline with no active hydroponic setups).']);
        }

        $count = $eligibleDevices->count();
        $ineligibleCount = count($validated['ids']) - $count;

        // Archive eligible devices
        Device::whereIn('id', $eligibleDevices->pluck('id'))->update(['is_archived' => true]);

        $message = "{$count} device(s) archived successfully.";
        if ($ineligibleCount > 0) {
            $message .= " {$ineligibleCount} device(s) did not meet archive requirements and were skipped.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk unarchive devices
     */
    public function bulkUnarchive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:devices,id',
        ]);

        $count = Device::whereIn('id', $validated['ids'])
            ->where('is_archived', true)
            ->update(['is_archived' => false]);

        return redirect()->back()->with('success', "{$count} device(s) restored successfully.");
    }

    /**
     * Unarchive a device
     */
    public function unarchive(string $id)
    {
        try {
            $device = Device::findOrFail($id);

            if (!$device->is_archived) {
                return redirect()->back()->withErrors(['error' => 'Device is not archived.']);
            }

            $device->update(['is_archived' => false]);

            return redirect()->back()->with('success', 'Device restored successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to restore device.']);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:150',
            'serial_number' => 'required|string|max:150|unique:devices,serial_number',
            'model' => 'nullable|string|max:100',
            'firmware_version' => 'nullable|string|max:50',
        ]);

        $device = Device::create([
            'device_name' => $validated['device_name'],
            'serial_number' => $validated['serial_number'],
            'model' => $validated['model'] ?? null,
            'firmware_version' => $validated['firmware_version'] ?? null,
            'status' => 'offline',
        ]);

        return redirect()->back()->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'device_name' => 'required|string|max:255',
                'status' => 'nullable|string|in:online,offline',
            ]);

            $device = Device::findOrFail($id);

            // Remove serial_number from validated data to prevent changes
            unset($validated['serial_number']);

            $device->update($validated);

            return redirect()->back()->with('success', 'Device updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update device.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
