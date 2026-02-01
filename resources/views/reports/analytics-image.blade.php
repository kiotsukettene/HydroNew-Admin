<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - {{ now()->format('F d, Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            min-height: 100vh;
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #667eea;
        }

        .header h1 {
            font-size: 42px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .header .subtitle {
            color: #64748b;
            font-size: 18px;
            margin-top: 10px;
        }

        .header .date {
            color: #94a3b8;
            font-size: 14px;
            margin-top: 8px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 25px;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .metric-card .icon {
            font-size: 32px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .metric-card .label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .metric-card .value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .metric-card .unit {
            font-size: 14px;
            opacity: 0.8;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-header {
            background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 100%);
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-header .icon {
            font-size: 28px;
        }

        .section-header h2 {
            color: #1e293b;
            font-size: 24px;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }

        .info-card .title {
            color: #64748b;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .info-card .content {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }

        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .stat-item {
            flex: 1;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .stat-item .label {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-item .value {
            color: #1e293b;
            font-size: 24px;
            font-weight: 700;
        }

        .filter-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin: 5px;
            border: 2px solid #bfdbfe;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 25px;
            border-top: 2px solid #e2e8f0;
            color: #94a3b8;
            font-size: 13px;
        }

        .footer .brand {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-online {
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
        }

        .status-offline {
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Analytics Dashboard</h1>
            <div class="subtitle">
                {{ ucfirst(str_replace('-', ' & ', $activeTab ?? 'Complete Overview')) }}
            </div>
            <div class="date">
                {{ now()->format('l, F d, Y - h:i A') }}
            </div>
        </div>

        <!-- Active Filters -->
        @if(isset($dateFrom) || isset($dateTo) || isset($deviceName))
        <div style="text-align: center; margin-bottom: 30px;">
            @if(isset($dateFrom) && isset($dateTo) && $dateFrom && $dateTo)
                <span class="filter-badge">
                    📅 {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                </span>
            @endif
            @if(isset($deviceName) && $deviceName)
                <span class="filter-badge">
                    🖥️ {{ $deviceName }}
                </span>
            @endif
            <span class="filter-badge">
                📈 {{ ucfirst($frequency ?? 'Monthly') }} View
            </span>
        </div>
        @endif

        <!-- Users & Devices Metrics -->
        @if(($activeTab ?? 'users-devices') === 'users-devices' || !isset($activeTab))
        <div class="section">
            <div class="section-header">
                <span class="icon">👥</span>
                <h2>Users & Devices</h2>
            </div>
            
            <div class="dashboard-grid">
                <div class="metric-card">
                    <div class="icon">👤</div>
                    <div class="label">Total Users</div>
                    <div class="value">{{ $usersDevices['users']['total'] ?? 0 }}</div>
                    <div class="unit">registered</div>
                </div>
                <div class="metric-card">
                    <div class="icon">✅</div>
                    <div class="label">Active Users</div>
                    <div class="value">{{ $usersDevices['users']['active'] ?? 0 }}</div>
                    <div class="unit">active</div>
                </div>
                <div class="metric-card">
                    <div class="icon">🖥️</div>
                    <div class="label">Total Devices</div>
                    <div class="value">{{ $usersDevices['devices']['total'] ?? 0 }}</div>
                    <div class="unit">registered</div>
                </div>
                <div class="metric-card">
                    <div class="icon">🟢</div>
                    <div class="label">Online Devices</div>
                    <div class="value">{{ $usersDevices['devices']['online'] ?? 0 }}</div>
                    <div class="unit">active now</div>
                </div>
            </div>

            <!-- Additional User Metrics -->
            <div class="info-grid" style="margin-top: 25px;">
                <div class="info-card">
                    <div class="title">📊 User Statistics</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="label">Inactive</div>
                            <div class="value">{{ $usersDevices['users']['inactive'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Archived</div>
                            <div class="value">{{ $usersDevices['users']['archived'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Without Device</div>
                            <div class="value">{{ $usersDevices['users']['without_devices'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="title">🖥️ Device Statistics</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="label">Online</div>
                            <div class="value">{{ $usersDevices['devices']['online'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Offline</div>
                            <div class="value">{{ $usersDevices['devices']['offline'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Total</div>
                            <div class="value">{{ $usersDevices['devices']['total'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration & Login Trends -->
            @if(!empty($usersDevices['registration_trend']))
            <h3 style="margin: 30px 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 600;">📈 User Registration Trend</h3>
            <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 12px; text-align: left; color: #475569; font-size: 13px;">Period</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">New Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usersDevices['registration_trend']->take(6) as $item)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px; color: #1e293b; font-size: 14px;">{{ $item['month'] ?? 'N/A' }}</td>
                            <td style="padding: 10px; text-align: right; color: #1e293b; font-weight: 600; font-size: 14px;">{{ $item['count'] ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!empty($usersDevices['login_activity_trend']))
            <h3 style="margin: 30px 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 600;">🔐 Login Activity</h3>
            <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 12px; text-align: left; color: #475569; font-size: 13px;">Period</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Unique Users</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Total Logins</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usersDevices['login_activity_trend']->take(6) as $item)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px; color: #1e293b; font-size: 14px;">{{ $item['date'] ?? 'N/A' }}</td>
                            <td style="padding: 10px; text-align: right; color: #1e293b; font-weight: 600; font-size: 14px;">{{ $item['unique_users'] ?? 0 }}</td>
                            <td style="padding: 10px; text-align: right; color: #0891b2; font-weight: 600; font-size: 14px;">{{ $item['total_logins'] ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Crops & Harvest Metrics -->
        @if(($activeTab ?? '') === 'crops-harvest-yield')
        <div class="section">
            <div class="section-header">
                <span class="icon">🌱</span>
                <h2>Crops & Harvest</h2>
            </div>
            
            <div class="dashboard-grid">
                <div class="metric-card">
                    <div class="icon">📅</div>
                    <div class="label">This Month</div>
                    <div class="value">{{ $cropsHarvestYield['harvest_this_month'] ?? 0 }}</div>
                    <div class="unit">crops</div>
                </div>
                <div class="metric-card">
                    <div class="icon">📆</div>
                    <div class="label">This Year</div>
                    <div class="value">{{ $cropsHarvestYield['harvest_this_year'] ?? 0 }}</div>
                    <div class="unit">harvested</div>
                </div>
                <div class="metric-card">
                    <div class="icon">⚖️</div>
                    <div class="label">Total Yield</div>
                    <div class="value">{{ number_format($cropsHarvestYield['total_yield_weight'] ?? 0, 1) }}</div>
                    <div class="unit">kg</div>
                </div>
                <div class="metric-card">
                    <div class="icon">📊</div>
                    <div class="label">Harvest Rate</div>
                    <div class="value">{{ number_format($cropsHarvestYield['harvest_rate'] ?? 0, 1) }}</div>
                    <div class="unit">%</div>
                </div>
            </div>

            <!-- Additional Crop Metrics -->
            <div class="info-grid" style="margin-top: 25px;">
                <div class="info-card">
                    <div class="title">📊 Yield Metrics</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="label">Total Weight</div>
                            <div class="value">{{ number_format($cropsHarvestYield['total_yield_weight'] ?? 0, 1) }} kg</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Total Count</div>
                            <div class="value">{{ $cropsHarvestYield['total_yield_count'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Avg/Setup</div>
                            <div class="value">{{ number_format($cropsHarvestYield['average_yield_per_setup'] ?? 0, 1) }} kg</div>
                        </div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="title">🌾 Most Grown Crop</div>
                    <div style="text-align: center; padding: 15px 0;">
                        <div style="font-size: 24px; font-weight: 700; color: #1e293b;">
                            {{ $cropsHarvestYield['most_grown_crop'] ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($cropsHarvestYield['popular_crops']))
            <h3 style="margin: 30px 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 600;">🌾 Popular Crops</h3>
            <div class="info-grid">
                @foreach($cropsHarvestYield['popular_crops']->take(4) as $crop)
                <div class="info-card">
                    <div class="title">{{ $crop->crop_name }}</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="label">Total Setups</div>
                            <div class="value">{{ $crop->count }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($cropsHarvestYield['monthly_harvest_trend']))
            <h3 style="margin: 30px 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 600;">📈 Harvest Trend</h3>
            <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 12px; text-align: left; color: #475569; font-size: 13px;">Period</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Harvested</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Weight (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cropsHarvestYield['monthly_harvest_trend']->take(6) as $item)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px; color: #1e293b; font-size: 14px;">{{ $item['month'] ?? 'N/A' }}</td>
                            <td style="padding: 10px; text-align: right; color: #1e293b; font-weight: 600; font-size: 14px;">{{ $item['harvested'] ?? 0 }}</td>
                            <td style="padding: 10px; text-align: right; color: #059669; font-weight: 600; font-size: 14px;">{{ number_format($item['total_weight'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Water Treatment Metrics -->
        @if(($activeTab ?? '') === 'water-treatment')
        <div class="section">
            <div class="section-header">
                <span class="icon">💧</span>
                <h2>Water Treatment</h2>
            </div>
            
            <div class="dashboard-grid">
                <div class="metric-card">
                    <div class="icon">🔄</div>
                    <div class="label">Total Cycles</div>
                    <div class="value">{{ $waterTreatment['total_cycles'] ?? 0 }}</div>
                    <div class="unit">completed</div>
                </div>
                <div class="metric-card">
                    <div class="icon">✅</div>
                    <div class="label">Successful</div>
                    <div class="value">{{ $waterTreatment['successful_cycles'] ?? 0 }}</div>
                    <div class="unit">cycles</div>
                </div>
                <div class="metric-card">
                    <div class="icon">📈</div>
                    <div class="label">Success Rate</div>
                    <div class="value">{{ number_format($waterTreatment['success_rate'] ?? 0, 1) }}%</div>
                    <div class="unit">rate</div>
                </div>
                <div class="metric-card">
                    <div class="icon">⏱️</div>
                    <div class="label">Avg Duration</div>
                    <div class="value">{{ number_format($waterTreatment['average_duration'] ?? 0, 1) }}</div>
                    <div class="unit">minutes</div>
                </div>
            </div>

            <!-- Additional Treatment Metrics -->
            <div class="info-grid" style="margin-top: 25px;">
                <div class="info-card">
                    <div class="title">📊 Cycle Statistics</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="label">Successful</div>
                            <div class="value" style="color: #059669;">{{ $waterTreatment['successful_cycles'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Failed</div>
                            <div class="value" style="color: #dc2626;">{{ $waterTreatment['failed_cycles'] ?? 0 }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Pending</div>
                            <div class="value" style="color: #f59e0b;">{{ $waterTreatment['pending_cycles'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="title">📉 Rates</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="label">Success</div>
                            <div class="value" style="color: #059669;">{{ number_format($waterTreatment['success_rate'] ?? 0, 1) }}%</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Failure</div>
                            <div class="value" style="color: #dc2626;">{{ number_format($waterTreatment['failure_rate'] ?? 0, 1) }}%</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Duration</div>
                            <div class="value">{{ number_format($waterTreatment['average_duration'] ?? 0, 1) }}m</div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($waterTreatment['stage_performance']))
            <h3 style="margin: 30px 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 600;">🔬 Stage Performance</h3>
            <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 12px; text-align: left; color: #475569; font-size: 13px;">Stage</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Count</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Pass Rate</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Avg pH</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waterTreatment['stage_performance'] as $stage)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px; color: #1e293b; font-size: 14px; font-weight: 600;">{{ $stage['stage_name'] }}</td>
                            <td style="padding: 10px; text-align: right; color: #1e293b; font-size: 14px;">{{ $stage['total_count'] }}</td>
                            <td style="padding: 10px; text-align: right; font-weight: 600; font-size: 14px; color: {{ $stage['pass_rate'] >= 80 ? '#059669' : '#dc2626' }};">
                                {{ number_format($stage['pass_rate'], 1) }}%
                            </td>
                            <td style="padding: 10px; text-align: right; color: #0891b2; font-weight: 600; font-size: 14px;">
                                {{ $stage['avg_ph'] ? number_format($stage['avg_ph'], 2) : 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!empty($waterTreatment['treatment_trends']))
            <h3 style="margin: 30px 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 600;">📊 Treatment Trends</h3>
            <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 12px; text-align: left; color: #475569; font-size: 13px;">Period</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Total Cycles</th>
                            <th style="padding: 12px; text-align: right; color: #475569; font-size: 13px;">Successful</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waterTreatment['treatment_trends']->take(6) as $item)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px; color: #1e293b; font-size: 14px;">{{ $item['date'] ?? 'N/A' }}</td>
                            <td style="padding: 10px; text-align: right; color: #1e293b; font-weight: 600; font-size: 14px;">{{ $item['cycle_count'] ?? 0 }}</td>
                            <td style="padding: 10px; text-align: right; color: #059669; font-weight: 600; font-size: 14px;">{{ $item['success_count'] ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="brand">HydroNew Admin Dashboard</div>
            <div>Automated Analytics Report</div>
            <div style="margin-top: 10px; opacity: 0.7;">
                © {{ now()->year }} HydroNew. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
