<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;

class AnalyticsExportService
{
    /**
     * Export analytics dashboard as PDF
     * 
     * @param string $url The full URL to capture
     * @param string|null $filename Optional custom filename
     * @return string Path to the generated PDF
     */
    public function exportToPdf(string $url, ?string $filename = null): string
    {
        $filename = $filename ?? 'analytics-report-' . now()->format('Y-m-d-His') . '.pdf';
        $path = storage_path('app/public/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(storage_path('app/public/exports'))) {
            mkdir(storage_path('app/public/exports'), 0755, true);
        }

        Browsershot::url($url)
            ->setNodeBinary('node')
            ->setNpmBinary('npm')
            ->waitUntilNetworkIdle()
            ->emulateMedia('print')
            ->margins(10, 10, 10, 10)
            ->format('A4')
            ->landscape()
            ->save($path);

        return $filename;
    }

    /**
     * Generate PDF from HTML content
     * 
     * @param string $html HTML content to convert
     * @param string|null $filename Optional custom filename
     * @return string Path to the generated PDF
     */
    public function htmlToPdf(string $html, ?string $filename = null): string
    {
        $filename = $filename ?? 'report-' . now()->format('Y-m-d-His') . '.pdf';
        $path = storage_path('app/public/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(storage_path('app/public/exports'))) {
            mkdir(storage_path('app/public/exports'), 0755, true);
        }

        try {
            Browsershot::html($html)
                ->setNodeBinary('node')
                ->setNpmBinary('npm')
                ->emulateMedia('print')
                ->margins(10, 10, 10, 10)
                ->format('A4')
                ->landscape()
                ->timeout(120)
                ->save($path);
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }

        return $filename;
    }

    /**
     * Clean up old export files (older than specified days)
     * 
     * @param int $days Number of days to keep files
     * @return int Number of files deleted
     */
    public function cleanupOldExports(int $days = 7): int
    {
        $path = storage_path('app/public/exports');
        $count = 0;

        if (!file_exists($path)) {
            return 0;
        }

        $files = glob($path . '/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                    unlink($file);
                    $count++;
                }
            }
        }

        return $count;
    }
}
