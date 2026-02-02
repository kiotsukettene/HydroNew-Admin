<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report - {{ now()->format('F d, Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 40px;
            font-size: 14px;
        }

        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1e40af;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header .meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        .section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #f1f5f9;
            padding: 12px 20px;
            border-left: 4px solid #2563eb;
            font-size: 20px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 20px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .metric-card .label {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .metric-card .value {
            color: #1e293b;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .metric-card .unit {
            color: #64748b;
            font-size: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 13px;
        }

        .data-table thead {
            background: #1e40af;
            color: white;
        }

        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .data-table tbody tr:hover {
            background: #f1f5f9;
        }

        .filter-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .filter-info strong {
            color: #1e40af;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-online {
            background: #d1fae5;
            color: #065f46;
        }

        .status-offline {
            background: #fee2e2;
            color: #991b1b;
        }

        .trend-up {
            color: #059669;
            font-weight: 600;
        }

        .trend-down {
            color: #dc2626;
            font-weight: 600;
        }

        h3 {
            margin: 30px 0 15px 0;
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }

        @media print {
            body {
                padding: 30px;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 Analytics Report</h1>
        <div class="meta">
            <div>
                <strong>Generated:</strong> {{ now()->format('F d, Y - h:i A') }}
            </div>
            <div>
                <strong>Report Type:</strong> {{ ucfirst(str_replace('-', ' & ', $activeTab ?? 'Overview')) }}
            </div>
        </div>
    </div>

    <!-- Filter Information -->
    @if(isset($dateFrom) || isset($dateTo) || isset($deviceName))
    <div class="filter-info">
        <strong>Applied Filters:</strong>
        @if(isset($dateFrom) && isset($dateTo) && $dateFrom && $dateTo)
            Date Range: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        @endif
        @if(isset($deviceName) && $deviceName)
            | Device: {{ $deviceName }}
        @endif
        | Frequency: {{ ucfirst($frequency ?? 'Monthly') }}
    </div>
    @endif

    <!-- Users & Devices Section -->
    @if(($activeTab ?? 'users-devices') === 'users-devices' || !isset($activeTab))
    <div class="section">
        <div class="section-title">Users & Devices Overview</div>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="label">Total Users</div>
                <div class="value">{{ $usersDevices['users']['total'] ?? 0 }}</div>
                <div class="unit">registered</div>
            </div>
            <div class="metric-card">
                <div class="label">Active Users</div>
                <div class="value">{{ $usersDevices['users']['active'] ?? 0 }}</div>
                <div class="unit">active</div>
            </div>
            <div class="metric-card">
                <div class="label">Total Devices</div>
                <div class="value">{{ $usersDevices['devices']['total'] ?? 0 }}</div>
                <div class="unit">registered</div>
            </div>
            <div class="metric-card">
                <div class="label">Online Devices</div>
                <div class="value">{{ $usersDevices['devices']['online'] ?? 0 }}</div>
                <div class="unit">active now</div>
            </div>
        </div>

        @if(!empty($usersDevices['registration_trend']))
        <h3>User Registration Trend</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>New Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usersDevices['registration_trend'] as $item)
                <tr>
                    <td>{{ $item['month'] ?? 'N/A' }}</td>
                    <td>{{ $item['count'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(!empty($usersDevices['login_activity_trend']))
        <h3>Login Activity</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Unique Users</th>
                    <th>Total Logins</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usersDevices['login_activity_trend'] as $item)
                <tr>
                    <td>{{ $item['date'] ?? 'N/A' }}</td>
                    <td>{{ $item['unique_users'] ?? 0 }}</td>
                    <td>{{ $item['total_logins'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <!-- Crops & Harvest Section -->
    @if(($activeTab ?? '') === 'crops-harvest-yield')
    <div class="section">
        <div class="section-title">Crops & Harvest Performance</div>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="label">Harvest This Month</div>
                <div class="value">{{ $cropsHarvestYield['harvest_this_month'] ?? 0 }}</div>
                <div class="unit">crops</div>
            </div>
            <div class="metric-card">
                <div class="label">Harvest This Year</div>
                <div class="value">{{ $cropsHarvestYield['harvest_this_year'] ?? 0 }}</div>
                <div class="unit">total</div>
            </div>
            <div class="metric-card">
                <div class="label">Total Yield Weight</div>
                <div class="value">{{ number_format($cropsHarvestYield['total_yield_weight'] ?? 0, 2) }}</div>
                <div class="unit">kg</div>
            </div>
            <div class="metric-card">
                <div class="label">Harvest Rate</div>
                <div class="value">{{ number_format($cropsHarvestYield['harvest_rate'] ?? 0, 1) }}%</div>
                <div class="unit">success</div>
            </div>
        </div>

        @if(!empty($cropsHarvestYield['popular_crops']))
        <h3>Popular Crops</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Crop Name</th>
                    <th>Total Setups</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cropsHarvestYield['popular_crops'] as $crop)
                <tr>
                    <td><strong>{{ $crop->crop_name }}</strong></td>
                    <td>{{ $crop->count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(!empty($cropsHarvestYield['monthly_harvest_trend']))
        <h3>Harvest Trend</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Harvested</th>
                    <th>Total Weight (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cropsHarvestYield['monthly_harvest_trend'] as $item)
                <tr>
                    <td>{{ $item['month'] ?? 'N/A' }}</td>
                    <td>{{ $item['harvested'] ?? 0 }}</td>
                    <td>{{ number_format($item['total_weight'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <!-- Water Treatment Section -->
    @if(($activeTab ?? '') === 'water-treatment')
    <div class="section">
        <div class="section-title">Water Treatment Analytics</div>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="label">Total Cycles</div>
                <div class="value">{{ $waterTreatment['total_cycles'] ?? 0 }}</div>
                <div class="unit">completed</div>
            </div>
            <div class="metric-card">
                <div class="label">Successful Cycles</div>
                <div class="value">{{ $waterTreatment['successful_cycles'] ?? 0 }}</div>
                <div class="unit">success</div>
            </div>
            <div class="metric-card">
                <div class="label">Success Rate</div>
                <div class="value">{{ number_format($waterTreatment['success_rate'] ?? 0, 1) }}%</div>
                <div class="unit">rate</div>
            </div>
            <div class="metric-card">
                <div class="label">Avg Duration</div>
                <div class="value">{{ number_format($waterTreatment['average_duration'] ?? 0, 1) }}</div>
                <div class="unit">minutes</div>
            </div>
        </div>

        @if(!empty($waterTreatment['stage_performance']))
        <h3>Stage Performance</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Stage Name</th>
                    <th>Total Count</th>
                    <th>Pass Rate</th>
                    <th>Avg pH</th>
                    <th>Avg Turbidity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($waterTreatment['stage_performance'] as $stage)
                <tr>
                    <td><strong>{{ $stage['stage_name'] }}</strong></td>
                    <td>{{ $stage['total_count'] }}</td>
                    <td>
                        <span class="{{ $stage['pass_rate'] >= 80 ? 'trend-up' : 'trend-down' }}">
                            {{ number_format($stage['pass_rate'], 1) }}%
                        </span>
                    </td>
                    <td>{{ $stage['avg_ph'] ? number_format($stage['avg_ph'], 2) : 'N/A' }}</td>
                    <td>{{ $stage['avg_turbidity'] ? number_format($stage['avg_turbidity'], 2) : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(!empty($waterTreatment['treatment_trends']))
        <h3>Treatment Trends</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Total Cycles</th>
                    <th>Successful</th>
                </tr>
            </thead>
            <tbody>
                @foreach($waterTreatment['treatment_trends'] as $item)
                <tr>
                    <td>{{ $item['date'] ?? 'N/A' }}</td>
                    <td>{{ $item['cycle_count'] ?? 0 }}</td>
                    <td>{{ $item['success_count'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>HydroNew Admin Dashboard</strong> - Analytics Report</p>
        <p>This report is generated automatically and contains confidential information.</p>
        <p>© {{ now()->year }} HydroNew. All rights reserved.</p>
    </div>
</body>
</html>
