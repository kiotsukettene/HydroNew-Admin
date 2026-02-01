# Browsershot Export Setup

## Overview
This project uses **Spatie Browsershot** to export analytics dashboards as PDF files or images.

## Installation
Already completed:
- ✅ Installed `spatie/browsershot` via Composer
- ✅ Installed `puppeteer` via npm
- ✅ Created `AnalyticsExportService`
- ✅ Created `AnalyticsExportController`
- ✅ Added export routes
- ✅ Added export buttons to analytics page
- ✅ Created storage/app/public/exports directory
- ✅ Created symbolic link (php artisan storage:link)

## Usage

### From the Analytics Page
1. Navigate to `/analytics`
2. Apply any filters you want (date range, device, frequency)
3. Click **"Export PDF"** or **"Export Image"** button
4. The file will be downloaded automatically

### Programmatic Usage

#### Export to PDF
```php
use App\Services\AnalyticsExportService;

$exportService = new AnalyticsExportService();

// From URL
$filename = $exportService->exportToPdf('https://example.com/analytics');

// From HTML
$html = view('reports.analytics', $data)->render();
$filename = $exportService->htmlToPdf($html, 'custom-report.pdf');
```

#### Export to Image
```php
$filename = $exportService->exportToImage('https://example.com/analytics', 'screenshot.png');
```

#### Cleanup Old Files
```php
// Delete files older than 7 days (default)
$deletedCount = $exportService->cleanupOldExports();

// Delete files older than 30 days
$deletedCount = $exportService->cleanupOldExports(30);
```

## API Endpoints

### Export Analytics as PDF
```
GET /analytics/export/pdf?tab={tab}&date_from={date}&date_to={date}&frequency={frequency}&device_id={id}
```

### Export Analytics as Image
```
GET /analytics/export/image?tab={tab}&date_from={date}&date_to={date}&frequency={frequency}&device_id={id}
```

## Configuration

### Browsershot Options
You can customize the export settings in `AnalyticsExportService.php`:

**PDF Options:**
- `format('A4')` - Paper size (A4, Letter, Legal, etc.)
- `landscape()` - Orientation (remove for portrait)
- `margins(10, 10, 10, 10)` - Margins in mm (top, right, bottom, left)
- `emulateMedia('print')` - Use print CSS styles
- `waitUntilNetworkIdle()` - Wait for all network requests to complete

**Image Options:**
- `windowSize(1920, 1080)` - Browser window size
- `deviceScaleFactor(2)` - For retina/high-DPI displays
- `fullPage()` - Capture entire page height

### Advanced Options
```php
Browsershot::url($url)
    ->setNodeBinary('node')
    ->setNpmBinary('npm')
    ->timeout(120) // 2 minutes timeout
    ->waitUntilNetworkIdle()
    ->setOption('args', ['--no-sandbox']) // For Docker/Linux
    ->dismissDialogs() // Auto-close dialogs
    ->blockUrls(['*google-analytics.com*']) // Block tracking
    ->save($path);
```

## Scheduled Cleanup

Add this to `app/Console/Kernel.php` to automatically clean up old exports:

```php
protected function schedule(Schedule $schedule)
{
    // Clean up exports older than 7 days, daily at 2am
    $schedule->call(function () {
        app(AnalyticsExportService::class)->cleanupOldExports(7);
    })->daily()->at('02:00');
}
```

## Troubleshooting

### "Puppeteer not found" Error
Run: `npm install puppeteer`

### "Node binary not found" Error
Make sure Node.js is installed and in your PATH. You can specify the path:
```php
Browsershot::url($url)
    ->setNodeBinary('C:\Program Files\nodejs\node.exe')
    ->setNpmBinary('C:\Program Files\nodejs\npm.cmd')
```

### Timeout Errors
Increase the timeout:
```php
Browsershot::url($url)->timeout(300) // 5 minutes
```

### Charts not rendering
Add `waitUntilNetworkIdle()` and increase timeout:
```php
Browsershot::url($url)
    ->waitUntilNetworkIdle()
    ->timeout(120)
```

### Docker/Linux Issues
Add the no-sandbox flag:
```php
->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
```

## File Storage
- Exported files are stored in: `storage/app/public/exports/`
- Files are automatically deleted after download
- Accessible via: `public/storage/exports/` (through symbolic link)

## Performance Tips
1. Use `waitUntilNetworkIdle()` for pages with charts/AJAX
2. Set reasonable timeouts based on page complexity
3. Schedule regular cleanup of old exports
4. Consider using queued jobs for large exports

## Security Notes
- Exports are automatically deleted after download
- Implement rate limiting for export endpoints
- Validate user permissions before allowing exports
- Consider adding CSRF protection
