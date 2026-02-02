<?php

namespace App\Http\Controllers\Devices;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sort', 'direction']);

        $query = Device::with('users')
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

        $devices = $query->paginate(10);

        $deviceCount = Device::where('is_archived', false)->count();

        $users = User::where('role', 'user')
            ->where('is_archived', false)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        return Inertia::render('devices/index', [
            'devices' => $devices,
            'deviceCount' => $deviceCount,
            'users' => $users,
            'filters' => $filters,
        ]);
    }

      public function archived(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        $devices = Device::with('users')
            ->where('is_archived', true)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('device_name', 'like', '%' . $search . '%')
                      ->orWhere('serial_number', 'like', '%' . $search . '%')
                      ->orWhereHas('users', function ($userQuery) use ($search) {
                          $userQuery->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                      });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $archivedCount = Device::where('is_archived', true)->count();

        return Inertia::render('devices/archive-devices', [
            'devices' => $devices,
            'archivedCount' => $archivedCount,
            'filters' => $filters,
        ]);
    }

    /**
     * Archive a device
     */
    public function archive(string $id)
    {
        try {
            $device = Device::findOrFail($id);
            
            if ($device->is_archived) {
                return redirect()->back()->withErrors(['error' => 'Device is already archived.']);
            }
            
            $device->update(['is_archived' => true]);

            return redirect()->back()->with('success', 'Device archived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to archive device.']);
        }
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

            return redirect()->back()->with('success', 'Device unarchived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to restore device.']);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
