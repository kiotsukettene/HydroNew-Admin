<?php

namespace App\Services;

use App\Models\User;
use App\Models\Device;
use App\Models\HydroponicSetup;
use App\Models\HydroponicYield;
use App\Models\HydroponicYieldGrade;
use App\Models\TreatmentReport;
use App\Models\TreatmentStage;
use App\Models\LoginHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    /**
     * Get users and devices overview analytics
     */
    public function getUsersDevicesAnalytics(): array
    {
        // User statistics
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')
            ->where('status', 'active')
            ->where('is_archived', false)
            ->count();
        $inactiveUsers = User::where('role', 'user')
            ->where('status', 'inactive')
            ->where('is_archived', false)
            ->count();
        $archivedUsers = User::where('role', 'user')
            ->where('is_archived', true)
            ->count();

        // Device statistics
        $totalDevices = Device::where('is_archived', false)->count();
        $onlineDevices = Device::where('status', 'online')
            ->where('is_archived', false)
            ->count();
        $offlineDevices = Device::where('status', 'offline')
            ->where('is_archived', false)
            ->count();

        // Users without devices
        $usersWithoutDevices = User::where('role', 'user')
            ->where('is_archived', false)
            ->whereDoesntHave('devices')
            ->count();

        // User registration trend (last 12 months)
        $registrationTrend = User::where('role', 'user')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month . '-01')->format('M Y'),
                    'count' => $item->count,
                ];
            });

        // Login activity trend (last 30 days)
        $loginActivityTrend = LoginHistory::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('COUNT(*) as total_logins')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'unique_users' => $item->unique_users,
                    'total_logins' => $item->total_logins,
                ];
            });

        return [
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
                'archived' => $archivedUsers,
                'without_devices' => $usersWithoutDevices,
            ],
            'devices' => [
                'total' => $totalDevices,
                'online' => $onlineDevices,
                'offline' => $offlineDevices,
            ],
            'registration_trend' => $registrationTrend,
            'login_activity_trend' => $loginActivityTrend,
        ];
    }

    /**
     * Get crops and harvest analytics
     */
    public function getCropsHarvestAnalytics(): array
    {
        // Setup status distribution
        $setupsByStatus = HydroponicSetup::where('is_archived', false)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Growth stage distribution
        $growthStageDistribution = HydroponicSetup::where('is_archived', false)
            ->whereNotNull('growth_stage')
            ->select('growth_stage', DB::raw('COUNT(*) as count'))
            ->groupBy('growth_stage')
            ->get()
            ->pluck('count', 'growth_stage');

        // Health status distribution
        $healthStatusDistribution = HydroponicSetup::where('is_archived', false)
            ->whereNotNull('health_status')
            ->select('health_status', DB::raw('COUNT(*) as count'))
            ->groupBy('health_status')
            ->get()
            ->pluck('count', 'health_status');

        // Popular crops (top 5)
        $popularCrops = HydroponicSetup::where('is_archived', false)
            ->select('crop_name', DB::raw('COUNT(*) as count'))
            ->groupBy('crop_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Most grown crop
        $mostGrownCrop = $popularCrops->first();

        // Crop type distribution for radar chart
        $cropTypeDistribution = $popularCrops->map(function ($crop) {
            return [
                'crop' => $crop->crop_name,
                'harvested' => $crop->count,
            ];
        });

        // Harvest rate
        $totalSetups = HydroponicSetup::where('is_archived', false)->count();
        $harvestedSetups = HydroponicSetup::where('is_archived', false)
            ->where('harvest_status', 'harvested')
            ->count();
        $harvestRate = $totalSetups > 0 ? round(($harvestedSetups / $totalSetups) * 100, 2) : 0;

        // Monthly harvest trends (last 12 months)
        $monthlyHarvestTrend = HydroponicSetup::where('harvest_status', 'harvested')
            ->whereNotNull('harvest_date')
            ->where('harvest_date', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(harvest_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as harvested')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month . '-01')->format('M'),
                    'harvested' => $item->harvested,
                ];
            });

        // Total harvest this month
        $harvestThisMonth = HydroponicSetup::where('harvest_status', 'harvested')
            ->whereMonth('harvest_date', Carbon::now()->month)
            ->whereYear('harvest_date', Carbon::now()->year)
            ->count();

        // Total harvest this year
        $harvestThisYear = HydroponicSetup::where('harvest_status', 'harvested')
            ->whereYear('harvest_date', Carbon::now()->year)
            ->count();

        return [
            'setups_by_status' => $setupsByStatus,
            'growth_stage_distribution' => $growthStageDistribution,
            'health_status_distribution' => $healthStatusDistribution,
            'popular_crops' => $popularCrops,
            'most_grown_crop' => $mostGrownCrop ? $mostGrownCrop->crop_name : null,
            'crop_type_distribution' => $cropTypeDistribution,
            'harvest_rate' => $harvestRate,
            'monthly_harvest_trend' => $monthlyHarvestTrend,
            'harvest_this_month' => $harvestThisMonth,
            'harvest_this_year' => $harvestThisYear,
        ];
    }

    /**
     * Get yield analytics
     */
    public function getYieldAnalytics(): array
    {
        // Total yield weight
        $totalYieldWeight = HydroponicYield::where('is_archived', false)
            ->sum('total_weight');

        // Total yield count
        $totalYieldCount = HydroponicYield::where('is_archived', false)
            ->sum('total_count');

        // Grade distribution
        $gradeDistribution = HydroponicYieldGrade::whereHas('hydroponic_yield', function ($query) {
            $query->where('is_archived', false);
        })
            ->select('grade', DB::raw('SUM(weight) as total_weight'), DB::raw('SUM(count) as total_count'))
            ->groupBy('grade')
            ->get();

        $totalGradeWeight = $gradeDistribution->sum('total_weight');
        $gradeStats = [];
        foreach ($gradeDistribution as $grade) {
            $gradeStats[$grade->grade] = [
                'weight' => round((float) $grade->total_weight, 2),
                'count' => $grade->total_count,
                'percentage' => $totalGradeWeight > 0 ? round(($grade->total_weight / $totalGradeWeight) * 100, 2) : 0,
            ];
        }

        // Average yield per setup
        $totalSetups = HydroponicSetup::where('harvest_status', 'harvested')->count();
        $averageYieldPerSetup = $totalSetups > 0 ? round($totalYieldWeight / $totalSetups, 2) : 0;

        // Top yielding crops
        $topYieldingCrops = HydroponicSetup::where('harvest_status', 'harvested')
            ->join('hydroponic_yields', 'hydroponic_setup.id', '=', 'hydroponic_yields.hydroponic_setup_id')
            ->where('hydroponic_yields.is_archived', false)
            ->select(
                'hydroponic_setup.crop_name',
                DB::raw('SUM(hydroponic_yields.total_weight) as total_weight'),
                DB::raw('COUNT(hydroponic_setup.id) as setup_count')
            )
            ->groupBy('hydroponic_setup.crop_name')
            ->orderByDesc('total_weight')
            ->limit(5)
            ->get();

        // Yield trends over time (last 12 months)
        $yieldTrends = HydroponicYield::where('is_archived', false)
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_weight) as total_weight')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month . '-01')->format('M Y'),
                    'total_weight' => round((float) $item->total_weight, 2),
                ];
            });

        return [
            'total_yield_weight' => round((float) $totalYieldWeight, 2),
            'total_yield_count' => $totalYieldCount,
            'grade_distribution' => $gradeStats,
            'average_yield_per_setup' => $averageYieldPerSetup,
            'top_yielding_crops' => $topYieldingCrops,
            'yield_trends' => $yieldTrends,
        ];
    }

    /**
     * Get water treatment analytics
     */
    public function getWaterTreatmentAnalytics(): array
    {
        // Total treatment cycles
        $totalCycles = TreatmentReport::count();

        // Success/failure rate
        $successfulCycles = TreatmentReport::where('final_status', 'success')->count();
        $failedCycles = TreatmentReport::where('final_status', 'failed')->count();
        $pendingCycles = TreatmentReport::where('final_status', 'pending')->count();

        $successRate = $totalCycles > 0 ? round(($successfulCycles / $totalCycles) * 100, 2) : 0;
        $failureRate = $totalCycles > 0 ? round(($failedCycles / $totalCycles) * 100, 2) : 0;

        // Average treatment duration (in minutes)
        $averageDuration = TreatmentReport::whereNotNull('end_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration')
            ->value('avg_duration');
        $averageDuration = $averageDuration ? round((float) $averageDuration, 2) : 0;

        // Stage performance breakdown
        $stagePerformance = TreatmentStage::select(
            'stage_name',
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN status = "passed" THEN 1 ELSE 0 END) as passed_count'),
            DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count'),
            DB::raw('AVG(pH) as avg_ph'),
            DB::raw('AVG(turbidity) as avg_turbidity'),
            DB::raw('AVG(TDS) as avg_tds')
        )
            ->groupBy('stage_name')
            ->get()
            ->map(function ($stage) {
                $passRate = $stage->total_count > 0 ? round(($stage->passed_count / $stage->total_count) * 100, 2) : 0;
                return [
                    'stage_name' => $stage->stage_name,
                    'total_count' => $stage->total_count,
                    'passed_count' => $stage->passed_count,
                    'failed_count' => $stage->failed_count,
                    'pass_rate' => $passRate,
                    'avg_ph' => $stage->avg_ph ? round((float) $stage->avg_ph, 2) : null,
                    'avg_turbidity' => $stage->avg_turbidity ? round((float) $stage->avg_turbidity, 2) : null,
                    'avg_tds' => $stage->avg_tds ? round((float) $stage->avg_tds, 2) : null,
                ];
            });

        // Treatment trends over time (last 30 days)
        $treatmentTrends = TreatmentReport::where('start_time', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('COUNT(*) as cycle_count'),
                DB::raw('SUM(CASE WHEN final_status = "success" THEN 1 ELSE 0 END) as success_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'cycle_count' => $item->cycle_count,
                    'success_count' => $item->success_count,
                ];
            });

        // Weekly filtration data (last 4 weeks)
        $weeklyFiltration = TreatmentReport::where('start_time', '>=', Carbon::now()->subWeeks(4))
            ->where('final_status', 'success')
            ->select(
                DB::raw('WEEK(start_time) as week'),
                DB::raw('COUNT(*) as cycles')
            )
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get()
            ->map(function ($item, $index) {
                return [
                    'week' => 'Week ' . ($index + 1),
                    'filtered' => $item->cycles * 50, // Assuming ~50L per cycle
                    'cycles' => $item->cycles,
                ];
            });

        return [
            'total_cycles' => $totalCycles,
            'successful_cycles' => $successfulCycles,
            'failed_cycles' => $failedCycles,
            'pending_cycles' => $pendingCycles,
            'success_rate' => $successRate,
            'failure_rate' => $failureRate,
            'average_duration' => $averageDuration,
            'stage_performance' => $stagePerformance,
            'treatment_trends' => $treatmentTrends,
            'weekly_filtration' => $weeklyFiltration,
        ];
    }
}

