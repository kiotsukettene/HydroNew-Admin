<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdminAnalyticsService;
use App\Services\AnalyticsExportService;

class TestAnalyticsExport extends Command
{
    protected $signature = 'test:analytics-export';
    protected $description = 'Test analytics export functionality';

    public function handle(AdminAnalyticsService $analyticsService, AnalyticsExportService $exportService)
    {
        $this->info('Testing Analytics Export...');

        try {
            // Fetch sample data
            $this->info('Fetching analytics data...');
            $usersDevices = $analyticsService->getUsersDevicesAnalytics(['frequency' => 'monthly']);
            $cropsHarvestYield = $analyticsService->getCropsHarvestYieldAnalytics(['frequency' => 'monthly']);
            $waterTreatment = $analyticsService->getWaterTreatmentAnalytics(['frequency' => 'monthly']);

            $this->info('✓ Data fetched successfully');
            $this->info('  - Total Users: ' . ($usersDevices['summary']['total_users'] ?? 0));
            $this->info('  - Total Devices: ' . ($usersDevices['summary']['total_devices'] ?? 0));
            $this->info('  - Harvest This Month: ' . ($cropsHarvestYield['metrics']['harvest_this_month'] ?? 0));

            // Test PDF generation
            $this->info('Generating PDF...');
            $html = view('reports.analytics-pdf', [
                'usersDevices' => $usersDevices,
                'cropsHarvestYield' => $cropsHarvestYield,
                'waterTreatment' => $waterTreatment,
                'dateFrom' => null,
                'dateTo' => null,
                'frequency' => 'monthly',
                'deviceName' => null,
                'activeTab' => 'users-devices',
            ])->render();

            $filename = $exportService->htmlToPdf($html, 'test-analytics-report.pdf');
            $path = storage_path('app/public/exports/' . $filename);

            if (file_exists($path)) {
                $this->info('✓ PDF generated successfully!');
                $this->info('  Path: ' . $path);
                $this->info('  Size: ' . number_format(filesize($path) / 1024, 2) . ' KB');
            } else {
                $this->error('✗ PDF file was not created');
                return Command::FAILURE;
            }

            // Test Image generation
            $this->info('Generating Image...');
            $htmlImage = view('reports.analytics-image', [
                'usersDevices' => $usersDevices,
                'cropsHarvestYield' => $cropsHarvestYield,
                'waterTreatment' => $waterTreatment,
                'dateFrom' => null,
                'dateTo' => null,
                'frequency' => 'monthly',
                'deviceName' => null,
                'activeTab' => 'users-devices',
            ])->render();

            $imageFilename = $exportService->htmlToImage($htmlImage, 'test-analytics-dashboard.png');
            $imagePath = storage_path('app/public/exports/' . $imageFilename);

            if (file_exists($imagePath)) {
                $this->info('✓ Image generated successfully!');
                $this->info('  Path: ' . $imagePath);
                $this->info('  Size: ' . number_format(filesize($imagePath) / 1024, 2) . ' KB');
            } else {
                $this->error('✗ Image file was not created');
                return Command::FAILURE;
            }

            $this->info('');
            $this->info('🎉 All exports generated successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
