<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    protected AdminAnalyticsService $analyticsService;

    public function __construct(AdminAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
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
            'frequency' => $request->input('frequency', 'monthly'), // weekly, monthly
            'device_id' => $request->input('device_id'), // for crops and water treatment filtering
        ];

        // Get all devices for the filter dropdown
        $devices = \App\Models\Device::where('is_archived', false)
            ->select('id', 'device_name', 'serial_number')
            ->orderBy('device_name')
            ->get();

        // Get all analytics data
        $usersDevicesData = $this->analyticsService->getUsersDevicesAnalytics($filters);
        $cropsHarvestYieldData = $this->analyticsService->getCropsHarvestYieldAnalytics($filters);
        $waterTreatmentData = $this->analyticsService->getWaterTreatmentAnalytics($filters);

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
