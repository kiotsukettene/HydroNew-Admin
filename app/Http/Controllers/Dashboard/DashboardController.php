<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\HydroponicYield;
use App\Models\HydroponicSetup;
use Illuminate\Http\Request;
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
        $totalUsers = User::where('roles', 'user')->count();
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

        return Inertia::render('dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalDevices' => $totalDevices,
                'totalHarvestedCrops' => $totalHarvestedCrops,
            ],
            'harvestStatus' => $harvestStatus,
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
