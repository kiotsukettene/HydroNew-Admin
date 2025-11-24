<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = Device::where('user_id', auth()->id())
            ->with('sensors')
            ->paginate(10);

        return Inertia::render('devices/index', [
            'devices' => $devices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('devices/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'serial_number' => ['required', 'string', 'max:150', 'unique:devices'],
            'status' => ['required', 'in:connected,not connected'],
        ]);

        $device = Device::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->route('devices.show', $device)
            ->with('success', 'Device created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        $this->authorize('view', $device);

        return Inertia::render('devices/show', [
            'device' => $device->load('sensors', 'sensors.readings'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        $this->authorize('update', $device);

        return Inertia::render('devices/edit', [
            'device' => $device,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'serial_number' => ['required', 'string', 'max:150', 'unique:devices,serial_number,' . $device->id],
            'status' => ['required', 'in:connected,not connected'],
        ]);

        $device->update($validated);

        return redirect()->route('devices.show', $device)
            ->with('success', 'Device updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $this->authorize('delete', $device);

        $device->delete();

        return redirect()->route('devices.index')
            ->with('success', 'Device deleted successfully');
    }
}
