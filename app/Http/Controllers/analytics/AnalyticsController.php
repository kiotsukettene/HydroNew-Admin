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
    public function index()
    {
        // Get all analytics data
        $usersDevicesData = $this->analyticsService->getUsersDevicesAnalytics();
        $cropsHarvestData = $this->analyticsService->getCropsHarvestAnalytics();
        $yieldData = $this->analyticsService->getYieldAnalytics();
        $waterTreatmentData = $this->analyticsService->getWaterTreatmentAnalytics();

        return Inertia::render('analytics/index', [
            'usersDevices' => $usersDevicesData,
            'cropsHarvest' => $cropsHarvestData,
            'yields' => $yieldData,
            'waterTreatment' => $waterTreatmentData,
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
     * Get crops and harvest analytics data (API endpoint)
     */
    public function getCropsHarvest(): JsonResponse
    {
        $data = $this->analyticsService->getCropsHarvestAnalytics();
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Get yield analytics data (API endpoint)
     */
    public function getYields(): JsonResponse
    {
        $data = $this->analyticsService->getYieldAnalytics();
        
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
