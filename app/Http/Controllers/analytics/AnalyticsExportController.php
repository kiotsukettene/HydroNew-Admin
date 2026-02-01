<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsExportService;
use App\Services\AdminAnalyticsService;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AnalyticsExportController extends Controller
{
    protected $exportService;
    protected $analyticsService;

    public function __construct(AnalyticsExportService $exportService, AdminAnalyticsService $analyticsService)
    {
        $this->exportService = $exportService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Export analytics as PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Get filters
            $filters = $request->only(['date_from', 'date_to', 'frequency', 'device_id', 'tab']);
            $activeTab = $filters['tab'] ?? 'users-devices';

            // Get device name if filtering by device
            $deviceName = null;
            if (!empty($filters['device_id'])) {
                $device = Device::find($filters['device_id']);
                $deviceName = $device ? $device->device_name : null;
            }

            // Fetch analytics data based on active tab
            $usersDevices = $this->analyticsService->getUsersDevicesAnalytics($filters);
            $cropsHarvestYield = $this->analyticsService->getCropsHarvestYieldAnalytics($filters);
            $waterTreatment = $this->analyticsService->getWaterTreatmentAnalytics($filters);

            // Render the Blade template
            $html = view('reports.analytics-pdf', [
                'usersDevices' => $usersDevices,
                'cropsHarvestYield' => $cropsHarvestYield,
                'waterTreatment' => $waterTreatment,
                'dateFrom' => $filters['date_from'] ?? null,
                'dateTo' => $filters['date_to'] ?? null,
                'frequency' => $filters['frequency'] ?? 'monthly',
                'deviceName' => $deviceName,
                'activeTab' => $activeTab,
            ])->render();

            // Generate PDF
            $filename = 'analytics-report-' . now()->format('Y-m-d-His') . '.pdf';
            $this->exportService->htmlToPdf($html, $filename);
            
            $path = storage_path('app/public/exports/' . $filename);

            // Return file download
            return Response::download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export analytics as image
     */
    public function exportImage(Request $request)
    {
        try {
            // Get filters
            $filters = $request->only(['date_from', 'date_to', 'frequency', 'device_id', 'tab']);
            $activeTab = $filters['tab'] ?? 'users-devices';

            // Get device name if filtering by device
            $deviceName = null;
            if (!empty($filters['device_id'])) {
                $device = Device::find($filters['device_id']);
                $deviceName = $device ? $device->device_name : null;
            }

            // Fetch analytics data based on active tab
            $usersDevices = $this->analyticsService->getUsersDevicesAnalytics($filters);
            $cropsHarvestYield = $this->analyticsService->getCropsHarvestYieldAnalytics($filters);
            $waterTreatment = $this->analyticsService->getWaterTreatmentAnalytics($filters);

            // Render the Blade template
            $html = view('reports.analytics-image', [
                'usersDevices' => $usersDevices,
                'cropsHarvestYield' => $cropsHarvestYield,
                'waterTreatment' => $waterTreatment,
                'dateFrom' => $filters['date_from'] ?? null,
                'dateTo' => $filters['date_to'] ?? null,
                'frequency' => $filters['frequency'] ?? 'monthly',
                'deviceName' => $deviceName,
                'activeTab' => $activeTab,
            ])->render();

            // Generate image from HTML
            $filename = 'analytics-dashboard-' . now()->format('Y-m-d-His') . '.png';
            $this->exportService->htmlToImage($html, $filename);
            
            $path = storage_path('app/public/exports/' . $filename);

            // Return file download
            return Response::download($path, $filename, [
                'Content-Type' => 'image/png',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate image: ' . $e->getMessage()
            ], 500);
        }
    }
}
