<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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
            // Try to detect Node.js binary path (common locations)
            $nodeBinary = $this->findNodeBinary();
            $npmBinary = $this->findNpmBinary();
            $chromeBinary = $this->findChromeBinary();

            $browsershot = Browsershot::html($html)
                ->emulateMedia('print')
                ->margins(10, 10, 10, 10)
                ->format('A4')
                ->landscape()
                ->timeout(120);

            // Only set Node/NPM binaries if found
            if ($nodeBinary) {
                $browsershot->setNodeBinary($nodeBinary);
            }
            if ($npmBinary) {
                $browsershot->setNpmBinary($npmBinary);
            }
            if ($chromeBinary) {
                $browsershot->setChromePath($chromeBinary);
            }

            $browsershot->save($path);

            // Verify file was created
            if (!file_exists($path)) {
                throw new \Exception('PDF file was not created. Check if Node.js and Puppeteer are installed.');
            }

        } catch (\Exception $e) {
            Log::error('PDF Export Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'node_binary' => $nodeBinary ?? 'not found',
                'npm_binary' => $npmBinary ?? 'not found',
                'chrome_binary' => $chromeBinary ?? 'not found',
            ]);
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }

        return $filename;
    }

    /**
     * Generate PDF from HTML content using DomPDF
     *
     * @param string $html HTML content to convert
     * @param string|null $filename Optional custom filename
     * @return string Path to the generated PDF
     */
    public function htmlToPdfDompdf(string $html, ?string $filename = null): string
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
            Log::error('DomPDF Export Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Failed to generate PDF with DomPDF: ' . $e->getMessage());
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

    /**
     * Find Node.js binary path
     */
    private function findNodeBinary(): ?string
    {
        $possiblePaths = [
            '/usr/bin/node',
            '/usr/local/bin/node',
            env('NODE_BINARY', 'node'),
            'node', // Fallback to PATH
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'node') {
                // Check if node is in PATH
                $output = shell_exec('which node 2>&1');
                if ($output && trim($output)) {
                    return trim($output);
                }
            } elseif (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Find NPM binary path
     */
    private function findNpmBinary(): ?string
    {
        $possiblePaths = [
            '/usr/bin/npm',
            '/usr/local/bin/npm',
            env('NPM_BINARY', 'npm'),
            'npm', // Fallback to PATH
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'npm') {
                // Check if npm is in PATH
                $output = shell_exec('which npm 2>&1');
                if ($output && trim($output)) {
                    return trim($output);
                }
            } elseif (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Find Chrome/Chromium binary path (for Puppeteer)
     */
    private function findChromeBinary(): ?string
    {
        // Check common Puppeteer Chrome locations
        $possiblePaths = [
            // Puppeteer's default location in node_modules
            base_path('node_modules/puppeteer/.local-chromium/linux-*/chrome-linux/chrome'),
            base_path('node_modules/puppeteer/.cache/chromium/*/chrome-linux/chrome'),
            // System Chrome locations
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/local/bin/chrome',
            '/usr/local/bin/chromium',
            // Environment variable
            env('CHROME_BINARY'),
        ];

        foreach ($possiblePaths as $path) {
            // Handle glob patterns
            if (strpos($path, '*') !== false) {
                $matches = glob($path);
                if (!empty($matches)) {
                    $path = $matches[0];
                } else {
                    continue;
                }
            }

            if ($path && file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }
}
