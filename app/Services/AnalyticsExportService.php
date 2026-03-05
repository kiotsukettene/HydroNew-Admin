<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class AnalyticsExportService
{
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
            // Generate PDF using DomPDF
            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape')
                ->setOption('margin_top', 10)
                ->setOption('margin_right', 10)
                ->setOption('margin_bottom', 10)
                ->setOption('margin_left', 10);

            $pdf->save($path);

            // Verify file was created
            if (!file_exists($path)) {
                throw new \Exception('PDF file was not created.');
            }

        } catch (\Exception $e) {
            Log::error('PDF Export Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
