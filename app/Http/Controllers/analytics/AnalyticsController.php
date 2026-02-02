<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    protected AdminAnalyticsService $analyticsService;

    public function __construct(AdminAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Return empty users/devices analytics structure (used when service fails)
     */
    private function emptyUsersDevicesAnalytics(): array
    {
        return [
            'users' => [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'archived' => 0,
                'without_devices' => 0,
            ],
            'devices' => [
                'total' => 0,
                'online' => 0,
                'offline' => 0,
            ],
            'registration_trend' => [],
            'login_activity_trend' => [],
        ];
    }

    /**
     * Return empty crops/harvest/yield analytics structure (used when service fails)
     */
    private function emptyCropsHarvestYieldAnalytics(): array
    {
        return [
            'setups_by_status' => [],
            'growth_stage_distribution' => [],
            'health_status_distribution' => [],
            'harvest_rate' => 0,
            'harvest_this_month' => 0,
            'harvest_this_year' => 0,
            'total_yield_weight' => 0,
            'total_yield_count' => 0,
            'average_yield_per_setup' => 0,
            'grade_distribution' => [],
            'popular_crops' => [],
            'most_grown_crop' => null,
            'crop_type_distribution' => [],
            'top_yielding_crops' => [],
            'monthly_harvest_trend' => [],
        ];
    }

    /**
     * Return empty water treatment analytics structure (used when service fails)
     */
    private function emptyWaterTreatmentAnalytics(): array
    {
        return [
            'total_cycles' => 0,
            'successful_cycles' => 0,
            'failed_cycles' => 0,
            'pending_cycles' => 0,
            'success_rate' => 0,
            'failure_rate' => 0,
            'average_duration' => 0,
            'stage_performance' => [],
            'treatment_trends' => [],
            'weekly_filtration' => [],
        ];
    }

    /**
     * Display the analytics page
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'frequency' => $request->input('frequency', 'monthly'),
            'device_id' => $request->input('device_id'),
        ];

        // Get all devices for the filter dropdown
        try {
            $devices = \App\Models\Device::where('is_archived', false)
                ->select('id', 'device_name', 'serial_number')
                ->orderBy('device_name')
                ->get();
        } catch (\Throwable $e) {
            Log::error('Analytics: devices query failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $devices = collect([]);
        }

        try {
            $usersDevicesData = $this->analyticsService->getUsersDevicesAnalytics($filters);
        } catch (\Throwable $e) {
            Log::error('Analytics: getUsersDevicesAnalytics failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $usersDevicesData = $this->emptyUsersDevicesAnalytics();
        }

        try {
            $cropsHarvestYieldData = $this->analyticsService->getCropsHarvestYieldAnalytics($filters);
        } catch (\Throwable $e) {
            Log::error('Analytics: getCropsHarvestYieldAnalytics failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $cropsHarvestYieldData = $this->emptyCropsHarvestYieldAnalytics();
        }

        try {
            $waterTreatmentData = $this->analyticsService->getWaterTreatmentAnalytics($filters);
        } catch (\Throwable $e) {
            Log::error('Analytics: getWaterTreatmentAnalytics failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $waterTreatmentData = $this->emptyWaterTreatmentAnalytics();
        }

        return Inertia::render('analytics/index', [
            'usersDevices' => $usersDevicesData,
            'cropsHarvestYield' => $cropsHarvestYieldData,
            'waterTreatment' => $waterTreatmentData,
            'devices' => $devices,
            'filters' => $filters,
        ]);
    }

    /**
     * Get users and devices analytics data (API endpoint)
     */
    public function getUsersDevices(): JsonResponse
    {
        $data = $this->analyticsService->getUsersDevicesAnalytics();
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Get crops, harvest, and yield analytics data (API endpoint)
     */
    public function getCropsHarvestYield(): JsonResponse
    {
        $data = $this->analyticsService->getCropsHarvestYieldAnalytics();
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Get water treatment analytics data (API endpoint)
     */
    public function getWaterTreatment(): JsonResponse
    {
        $data = $this->analyticsService->getWaterTreatmentAnalytics();
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
