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
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: #ffffff;
            padding: 40px 30px;
            font-size: 10px;
        }

        .header {
            border-bottom: 3px solid #000000;
            padding: 0 0 15px 0;
            margin-bottom: 30px;
            color: #000000;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            color: #000000;
        }

        .header .meta {
            font-size: 10px;
            letter-spacing: 0.3px;
            color: #666666;
        }

        .header .meta strong {
            font-weight: 600;
            color: #000000;
        }

        .section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 18px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
            letter-spacing: 0.3px;
        }

        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 8px 0;
        }

        .metrics-row {
            display: table-row;
        }

        .metric-card {
            display: table-cell;
            background: #f8f9fa;
            border-left: 3px solid #000000;
            padding: 15px 12px;
            text-align: center;
            width: 25%;
            vertical-align: middle;
        }

        .metric-card .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            color: #666666;
            font-weight: 600;
        }

        .metric-card .value {
            font-size: 20px;
            font-weight: 600;
            margin: 6px 0;
            color: #000000;
        }

        .metric-card .unit {
            font-size: 7px;
            color: #999999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid #dddddd;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            font-size: 10px;
            border: 1px solid #dddddd;
        }

        .data-table th {
            background: #f5f5f5;
            color: #000000;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 9px;
        }

        .data-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .data-table tbody tr:hover {
            background: #f0f0f0;
        }

        .data-table td strong {
            font-weight: 600;
            color: #000000;
        }

        .filter-info {
            background: #f5f5f5;
            border-left: 3px solid #000000;
            padding: 12px 15px;
            margin-bottom: 25px;
            font-size: 9px;
            color: #333333;
            line-height: 1.7;
        }

        .filter-info strong {
            font-weight: 600;
            color: #000000;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 8px;
            color: #666666;
        }

        .footer p {
            margin: 3px 0;
        }

        .footer strong {
            color: #000000;
            font-weight: 600;
        }

        h3 {
            margin: 20px 0 10px 0;
            font-size: 12px;
            font-weight: 600;
            color: #000000;
            letter-spacing: 0.2px;
        }

        @media print {
            body {
                padding: 30px 20px;
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
        <h1>Analytics Report</h1>
        <div class="meta">
            <strong>Generated:</strong> {{ now()->format('F d, Y - h:i A') }}
            <span style="margin-left: 20px;"><strong>Report Type:</strong> {{ ucfirst(str_replace('-', ' & ', $activeTab ?? 'Overview')) }}</span>
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
            <div class="metrics-row">
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
            <div class="metrics-row">
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
                    <div class="unit">g</div>
                </div>
                <div class="metric-card">
                    <div class="label">Harvest Rate</div>
                    <div class="value">{{ number_format($cropsHarvestYield['harvest_rate'] ?? 0, 1) }}%</div>
                    <div class="unit">success</div>
                </div>
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
                    <th>Total Weight (g)</th>
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
            <div class="metrics-row">
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
                    <td>{{ number_format($stage['pass_rate'], 1) }}%</td>
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
        <p>© {{ now()->year }} HydroNew. All rights reserved.</p>
    </div>
</body>
</html>
