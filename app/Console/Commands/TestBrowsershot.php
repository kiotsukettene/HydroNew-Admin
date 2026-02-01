<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnalyticsExportService;

class TestBrowsershot extends Command
{
    protected $signature = 'test:browsershot';
    protected $description = 'Test Browsershot installation';

    public function handle(AnalyticsExportService $exportService)
    {
        $this->info('Testing Browsershot installation...');

        try {
            // Test simple HTML to PDF
            $html = '<html><body><h1>Browsershot Test</h1><p>If you can see this PDF, Browsershot is working correctly!</p></body></html>';
            
            $this->info('Generating test PDF from HTML...');
            $filename = $exportService->htmlToPdf($html, 'browsershot-test.pdf');
            
            $path = storage_path('app/public/exports/' . $filename);
            
            if (file_exists($path)) {
                $this->info('✓ Success! Test PDF created at: ' . $path);
                $this->info('File size: ' . filesize($path) . ' bytes');
                return Command::SUCCESS;
            } else {
                $this->error('✗ Failed! PDF file was not created.');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
