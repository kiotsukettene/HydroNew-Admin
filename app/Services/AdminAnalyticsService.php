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
     * Generate all periods between two dates based on frequency
     */
    private function generatePeriods(Carbon $dateFrom, Carbon $dateTo, string $frequency): array
    {
        $periods = [];
        $current = $dateFrom->copy();
        
        if ($frequency === 'weekly') {
            while ($current->lte($dateTo)) {
                $periods[] = $current->format('Y-W');
                $current->addWeek();
            }
        } else {
            while ($current->lte($dateTo)) {
                $periods[] = $current->format('Y-m');
                $current->addMonth();
            }
        }
        
        return $periods;
    }

    /**
     * Get users and devices overview analytics
     */
    public function getUsersDevicesAnalytics(array $filters = []): array
    {
        // Set default date range if not provided
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subMonths(12)->startOfDay();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfDay();
        $frequency = $filters['frequency'] ?? 'monthly';

        // Convert to Carbon instances if strings
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo)->endOfDay();
        }

        // User statistics
        $totalUsers = User::regularUsers()->count();
        $activeUsers = User::regularUsers()->active()->count();
        $inactiveUsers = User::regularUsers()->inactive()->count();
        $archivedUsers = User::regularUsers()->archived()->count();

        // Device statistics
        $totalDevices = Device::where('is_archived', false)->count();
        $onlineDevices = Device::where('status', 'online')
            ->where('is_archived', false)
            ->count();
        $offlineDevices = Device::where('status', 'offline')
            ->where('is_archived', false)
            ->count();

        // Users without devices
        $usersWithoutDevices = User::regularUsers()
            ->whereDoesntHave('devices')
            ->count();

        // User registration trend based on frequency
        $dateFormat = $frequency === 'weekly' ? '%Y-%u' : '%Y-%m';
        $labelFormat = $frequency === 'weekly' ? 'W%W %Y' : 'M Y';
        
        $registrationData = User::regularUsers()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        // Generate all periods and fill with data
        $allPeriods = $this->generatePeriods($dateFrom, $dateTo, $frequency);
        $registrationTrend = collect($allPeriods)->map(function ($period) use ($registrationData, $frequency, $labelFormat) {
            if ($frequency === 'weekly') {
                // Parse week format (YYYY-WW)
                list($year, $week) = explode('-', $period);
                $date = Carbon::now()->setISODate($year, $week);
                $label = $date->format($labelFormat);
            } else {
                $label = Carbon::parse($period . '-01')->format($labelFormat);
            }
            
            return [
                'month' => $label,
                'count' => $registrationData->get($period)->count ?? 0,
            ];
        });

        // Login activity trend based on frequency and date range
        $loginDateFormat = $frequency === 'weekly' ? '%Y-%u' : '%Y-%m-%d';
        $loginLabelFormat = $frequency === 'weekly' ? 'W%W' : 'M d';
        
        $loginData = LoginHistory::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$loginDateFormat}') as period"),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('COUNT(*) as total_logins')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        // For login activity, generate all periods
        $allLoginPeriods = $this->generatePeriods($dateFrom, $dateTo, $frequency);
        $loginActivityTrend = collect($allLoginPeriods)->map(function ($period) use ($loginData, $frequency, $loginLabelFormat) {
            if ($frequency === 'weekly') {
                list($year, $week) = explode('-', $period);
                $date = Carbon::now()->setISODate($year, $week);
                $label = $date->format($loginLabelFormat);
            } else {
                $label = Carbon::parse($period . '-01')->format($loginLabelFormat);
            }
            
            return [
                'date' => $label,
                'unique_users' => $loginData->get($period)->unique_users ?? 0,
                'total_logins' => $loginData->get($period)->total_logins ?? 0,
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
     * Get combined crops, harvest, and yield analytics
     */
    public function getCropsHarvestYieldAnalytics(array $filters = []): array
    {
        // Set default date range if not provided
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subMonths(12)->startOfDay();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfDay();
        $frequency = $filters['frequency'] ?? 'monthly';
        $deviceId = $filters['device_id'] ?? null;

        // Convert to Carbon instances if strings
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo)->endOfDay();
        }

        // === HARVEST METRICS ===
        
        // Setup status distribution
        $setupsByStatus = HydroponicSetup::where('is_archived', false)
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Growth stage distribution
        $growthStageDistribution = HydroponicSetup::where('is_archived', false)
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereNotNull('growth_stage')
            ->select('growth_stage', DB::raw('COUNT(*) as count'))
            ->groupBy('growth_stage')
            ->get()
            ->pluck('count', 'growth_stage');

        // Health status distribution
        $healthStatusDistribution = HydroponicSetup::where('is_archived', false)
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereNotNull('health_status')
            ->select('health_status', DB::raw('COUNT(*) as count'))
            ->groupBy('health_status')
            ->get()
            ->pluck('count', 'health_status');

        // Harvest rate
        $totalSetups = HydroponicSetup::where('is_archived', false)
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->count();
        $harvestedSetups = HydroponicSetup::where('is_archived', false)
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->where('harvest_status', 'harvested')
            ->count();
        $harvestRate = $totalSetups > 0 ? round(($harvestedSetups / $totalSetups) * 100, 2) : 0;

        // Total harvest this month
        $harvestThisMonth = HydroponicSetup::where('harvest_status', 'harvested')
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereMonth('harvest_date', Carbon::now()->month)
            ->whereYear('harvest_date', Carbon::now()->year)
            ->count();

        // Total harvest this year
        $harvestThisYear = HydroponicSetup::where('harvest_status', 'harvested')
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereYear('harvest_date', Carbon::now()->year)
            ->count();

        // === YIELD METRICS ===
        
        // Total yield weight
        $totalYieldWeight = HydroponicYield::where('is_archived', false)
            ->when($deviceId, function($query) use ($deviceId) {
                $query->whereHas('hydroponic_setup', fn($q) => $q->where('device_id', $deviceId));
            })
            ->sum('total_weight');

        // Total yield count
        $totalYieldCount = HydroponicYield::where('is_archived', false)
            ->when($deviceId, function($query) use ($deviceId) {
                $query->whereHas('hydroponic_setup', fn($q) => $q->where('device_id', $deviceId));
            })
            ->sum('total_count');

        // Average yield per setup
        $averageYieldPerSetup = $harvestedSetups > 0 ? round($totalYieldWeight / $harvestedSetups, 2) : 0;

        // Grade distribution
        $gradeDistribution = HydroponicYieldGrade::whereHas('hydroponic_yield', function ($query) use ($deviceId) {
            $query->where('is_archived', false);
            if ($deviceId) {
                $query->whereHas('hydroponic_setup', fn($q) => $q->where('device_id', $deviceId));
            }
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

        // === COMBINED CROP INSIGHTS ===
        
        // Popular crops (top 5)
        $popularCrops = HydroponicSetup::where('is_archived', false)
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
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

        // Top yielding crops
        $topYieldingCrops = HydroponicSetup::where('harvest_status', 'harvested')
            ->when($deviceId, fn($query) => $query->where('hydroponic_setup.device_id', $deviceId))
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

        // === COMBINED TRENDS ===
        
        // Combined harvest and yield trends based on frequency
        $dateFormat = $frequency === 'weekly' ? '%Y-%u' : '%Y-%m';
        $labelFormat = $frequency === 'weekly' ? 'W%W' : 'M';
        
        $monthlyHarvestData = HydroponicSetup::where('harvest_status', 'harvested')
            ->when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereNotNull('harvest_date')
            ->whereBetween('harvest_date', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(harvest_date, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as harvested')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        $monthlyYieldData = HydroponicYield::where('is_archived', false)
            ->when($deviceId, function($query) use ($deviceId) {
                $query->whereHas('hydroponic_setup', fn($q) => $q->where('device_id', $deviceId));
            })
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('SUM(total_weight) as total_weight')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        // Generate all periods and merge harvest and yield data
        $allPeriods = $this->generatePeriods($dateFrom, $dateTo, $frequency);
        $monthlyHarvestTrend = collect($allPeriods)->map(function ($period) use ($monthlyHarvestData, $monthlyYieldData, $frequency, $labelFormat) {
            if ($frequency === 'weekly') {
                list($year, $week) = explode('-', $period);
                $date = Carbon::now()->setISODate($year, $week);
                $label = $date->format($labelFormat);
            } else {
                $label = Carbon::parse($period . '-01')->format($labelFormat);
            }
            
            return [
                'month' => $label,
                'harvested' => $monthlyHarvestData->get($period)->harvested ?? 0,
                'total_weight' => isset($monthlyYieldData->get($period)->total_weight) 
                    ? round((float) $monthlyYieldData->get($period)->total_weight, 2) 
                    : 0,
            ];
        })->values();

        return [
            // Harvest metrics
            'setups_by_status' => $setupsByStatus,
            'growth_stage_distribution' => $growthStageDistribution,
            'health_status_distribution' => $healthStatusDistribution,
            'harvest_rate' => $harvestRate,
            'harvest_this_month' => $harvestThisMonth,
            'harvest_this_year' => $harvestThisYear,
            
            // Yield metrics
            'total_yield_weight' => round((float) $totalYieldWeight, 2),
            'total_yield_count' => $totalYieldCount,
            'average_yield_per_setup' => $averageYieldPerSetup,
            'grade_distribution' => $gradeStats,
            
            // Combined crop insights
            'popular_crops' => $popularCrops,
            'most_grown_crop' => $mostGrownCrop ? $mostGrownCrop->crop_name : null,
            'crop_type_distribution' => $cropTypeDistribution,
            'top_yielding_crops' => $topYieldingCrops,
            
            // Combined trends
            'monthly_harvest_trend' => $monthlyHarvestTrend,
        ];
    }

    /**
     * Get water treatment analytics
     */
    public function getWaterTreatmentAnalytics(array $filters = []): array
    {
        // Set default date range if not provided
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subMonths(12)->startOfDay();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfDay();
        $frequency = $filters['frequency'] ?? 'monthly';
        $deviceId = $filters['device_id'] ?? null;

        // Convert to Carbon instances if strings
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo)->endOfDay();
        }

        // Total treatment cycles
        $totalCycles = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->count();

        // Success/failure rate
        $successfulCycles = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->where('final_status', 'success')->count();
        $failedCycles = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->where('final_status', 'failed')->count();
        $pendingCycles = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->where('final_status', 'pending')->count();

        $successRate = $totalCycles > 0 ? round(($successfulCycles / $totalCycles) * 100, 2) : 0;
        $failureRate = $totalCycles > 0 ? round(($failedCycles / $totalCycles) * 100, 2) : 0;

        // Average treatment duration (in minutes)
        $averageDuration = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereNotNull('end_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration')
            ->value('avg_duration');
        $averageDuration = $averageDuration ? round((float) $averageDuration, 2) : 0;

        // Stage performance breakdown
        $stagePerformance = TreatmentStage::when($deviceId, function($query) use ($deviceId) {
                $query->whereHas('treatment_report', fn($q) => $q->where('device_id', $deviceId));
            })
            ->select(
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

        // Treatment trends over time based on frequency
        $dateFormat = $frequency === 'weekly' ? '%Y-%u' : '%Y-%m';
        $labelFormat = $frequency === 'weekly' ? 'W%W' : 'M';
        
        $treatmentData = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereBetween('start_time', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(start_time, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as cycle_count'),
                DB::raw('SUM(CASE WHEN final_status = "success" THEN 1 ELSE 0 END) as success_count')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        // Generate all periods and fill with data
        $allPeriods = $this->generatePeriods($dateFrom, $dateTo, $frequency);
        $treatmentTrends = collect($allPeriods)->map(function ($period) use ($treatmentData, $frequency, $labelFormat) {
            if ($frequency === 'weekly') {
                list($year, $week) = explode('-', $period);
                $date = Carbon::now()->setISODate($year, $week);
                $label = $date->format($labelFormat);
            } else {
                $label = Carbon::parse($period . '-01')->format($labelFormat);
            }
            
            return [
                'date' => $label,
                'cycle_count' => $treatmentData->get($period)->cycle_count ?? 0,
                'success_count' => $treatmentData->get($period)->success_count ?? 0,
            ];
        });

        // Weekly filtration data based on frequency
        $filtrationData = TreatmentReport::when($deviceId, fn($query) => $query->where('device_id', $deviceId))
            ->whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('final_status', 'success')
            ->select(
                DB::raw("DATE_FORMAT(start_time, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as cycles')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        // Generate all periods for filtration data
        $weeklyFiltration = collect($allPeriods)->map(function ($period) use ($filtrationData, $frequency, $labelFormat) {
            if ($frequency === 'weekly') {
                list($year, $week) = explode('-', $period);
                $date = Carbon::now()->setISODate($year, $week);
                $label = $date->format($labelFormat);
            } else {
                $label = Carbon::parse($period . '-01')->format($labelFormat);
            }
            
            $cycles = $filtrationData->get($period)->cycles ?? 0;
            
            return [
                'week' => $label,
                'filtered' => $cycles * 50, // Assuming ~50L per cycle
                'cycles' => $cycles,
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

