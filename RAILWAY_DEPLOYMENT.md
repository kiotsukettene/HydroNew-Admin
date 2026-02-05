# Railway Deployment - PDF Export Setup

## Issue
The PDF export feature requires Node.js and Puppeteer to be installed on the server.

## Solution

### 1. Node.js Installation
The `nixpacks.toml` file has been created to ensure Node.js 20.x is installed during the build process.

### 2. Puppeteer Installation
Puppeteer is already in `package.json` dependencies. The build process will:
- Install Puppeteer via `npm ci`
- Download Chrome browser via `npx puppeteer browsers install chrome`

### 3. Environment Variables (Optional)
You can set these in Railway's environment variables if needed:

- `NODE_BINARY`: Custom path to Node.js binary (default: auto-detect)
- `NPM_BINARY`: Custom path to NPM binary (default: auto-detect)
- `CHROME_BINARY`: Custom path to Chrome/Chromium binary (default: auto-detect)

### 4. Storage Permissions
Ensure the storage directory is writable:
```bash
chmod -R 755 storage/app/public/exports
```

### 5. Verify Installation
After deployment, check the logs to ensure:
- Node.js is installed: `node --version`
- Puppeteer Chrome is downloaded: Check `node_modules/puppeteer/.local-chromium/`

## Troubleshooting

If PDF export still fails:

1. **Check Railway logs** for specific error messages
2. **Verify Node.js is available**: The service will log the detected paths
3. **Check Chrome binary**: The service will attempt to find Puppeteer's Chrome automatically
4. **Storage permissions**: Ensure `storage/app/public/exports` exists and is writable

## Testing Locally

To test the export functionality locally:
```bash
# Install dependencies
npm install
npx puppeteer browsers install chrome

# Test export
php artisan test:analytics-export
```
