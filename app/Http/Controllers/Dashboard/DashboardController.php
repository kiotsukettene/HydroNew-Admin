<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\HydroponicYield;
use App\Models\HydroponicSetup;
use App\Models\SensorSystem;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and harvest status
     */
    public function index()
    {
        // Get total counts
        $totalUsers = User::where('role', 'user')->count();
        $totalDevices = Device::where('is_archived', false)->count();
        $totalHarvestedCrops = HydroponicYield::count();

        // Get the most recent active hydroponic setup for harvest status
        $activeSetup = HydroponicSetup::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();

        // Calculate harvest status data
        $harvestStatus = [
            'waterTankLevel' => 85, // This would come from actual sensor data
            'currentGrowthStage' => $activeSetup ? $this->getGrowthStage($activeSetup) : 'No active setup',
            'estimatedHarvestDate' => $activeSetup && $activeSetup->expected_harvest_date 
                ? Carbon::parse($activeSetup->expected_harvest_date)->format('M d, Y') 
                : null,
            'daysRemaining' => $activeSetup && $activeSetup->expected_harvest_date
                ? Carbon::now()->diffInDays(Carbon::parse($activeSetup->expected_harvest_date), false)
                : null,
        ];

        // Get all devices for the filter dropdown
        $devices = Device::where('is_archived', false)
            ->select('id', 'device_name', 'status')
            ->get();

        return Inertia::render('dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalDevices' => $totalDevices,
                'totalHarvestedCrops' => $totalHarvestedCrops,
            ],
            'harvestStatus' => $harvestStatus,
            'devices' => $devices,
        ]);
    }

    /**
     * Get sensor systems for a specific device
     */
    public function getSensorSystems(Request $request): JsonResponse
    {
        $deviceId = $request->query('device_id');
        
        if (!$deviceId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device ID is required',
            ], 400);
        }

        $sensorSystems = SensorSystem::where('device_id', $deviceId)
            ->where('is_active', true)
            ->select('id', 'system_type', 'name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sensorSystems,
        ]);
    }

    /**
     * Get pH and TDS readings for a specific sensor system
     */
    public function getPhTdsReadings(Request $request): JsonResponse
    {
        $sensorSystemId = $request->query('sensor_system_id');
        $days = $request->query('days', 7);

        if (!$sensorSystemId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sensor system ID is required',
            ], 400);
        }

        $dateFrom = Carbon::now()->subDays($days)->startOfDay();

        $readings = SensorReading::where('sensor_system_id', $sensorSystemId)
            ->where('reading_time', '>=', $dateFrom)
            ->whereNotNull('ph')
            ->whereNotNull('tds')
            ->orderBy('reading_time', 'asc')
            ->select('ph', 'tds', 'reading_time')
            ->get()
            ->map(function ($reading) {
                return [
                    'date' => $reading->reading_time->format('Y-m-d H:i:s'),
                    'pH' => round((float) $reading->ph, 2),
                    'TDS' => round((float) $reading->tds, 2),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $readings,
        ]);
    }

    /**
     * Determine growth stage based on setup dates
     */
    private function getGrowthStage($setup)
    {
        if (!$setup->planting_date) {
            return 'Not planted';
        }

        $daysSincePlanting = Carbon::parse($setup->planting_date)->diffInDays(Carbon::now());

        // Basic growth stage estimation (adjust based on your crop types)
        if ($daysSincePlanting < 7) {
            return 'Germination';
        } elseif ($daysSincePlanting < 14) {
            return 'Seedling';
        } elseif ($daysSincePlanting < 30) {
            return 'Vegetative';
        } elseif ($daysSincePlanting < 45) {
            return 'Pre-harvest';
        } else {
            return 'Mature';
        }
    }
}
