import AppLayout from '@/layouts/app-layout'
import { Head, usePage, router } from '@inertiajs/react'
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart'
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, RadarChart, PolarAngleAxis, PolarGrid, Radar, LineChart, Line, Legend } from 'recharts'
import { Users, Cast, Leaf, Waves, TrendingUp, TrendingDown, Activity, Calendar, Filter, FileDown } from 'lucide-react'
import {
  TextureCardContent,
  TextureCardStyled
} from '@/components/ui/texture-card'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { FiltrationCyclesCard } from '@/components/filtration-cycles-card'
import { UsersDevicesAnalytics, CropsHarvestYieldAnalytics, WaterTreatmentAnalytics } from '@/types/analytics'
import { Device } from '@/types/device'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useState } from 'react'

export default function Analytics() {
  const { usersDevices, cropsHarvestYield, waterTreatment, devices = [], filters = { frequency: 'monthly' } } = usePage<{
    usersDevices: UsersDevicesAnalytics;
    cropsHarvestYield: CropsHarvestYieldAnalytics;
    waterTreatment: WaterTreatmentAnalytics;
    devices?: Device[];
    filters?: { date_from?: string; date_to?: string; frequency: string; device_id?: number };
  }>().props;

  const [activeTab, setActiveTab] = useState('users-devices');
  const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
  const [dateTo, setDateTo] = useState(filters?.date_to || '');
  const [frequency, setFrequency] = useState(filters?.frequency || 'monthly');
  const [deviceId, setDeviceId] = useState<string>(filters?.device_id?.toString() || 'all');

  // Safety check for devices
  const devicesList = devices;

  const handleApplyFilters = () => {
    router.get('/analytics', {
      date_from: dateFrom,
      date_to: dateTo,
      frequency: frequency,
      device_id: deviceId !== 'all' ? deviceId : undefined,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleResetFilters = () => {
    setDateFrom('');
    setDateTo('');
    setFrequency('monthly');
    setDeviceId('all');
    router.get('/analytics', {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleExportPdf = () => {
    const params = new URLSearchParams({
      tab: activeTab,
      ...(dateFrom && { date_from: dateFrom }),
      ...(dateTo && { date_to: dateTo }),
      frequency: frequency,
      ...(deviceId !== 'all' && { device_id: deviceId }),
    });

    window.open(`/analytics/export/pdf?${params.toString()}`, '_blank');
  };

  return (
    <AppLayout title="">
      <Head title="Analytics" />
      <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">

        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-foreground">Analytics Overview</h1>
            <p className="text-sm text-muted-foreground mt-1">Track your system performance and insights</p>
          </div>

          {/* Compact Filter Controls - Inspired by Devices Page */}
          <div className="flex gap-2">
            {/* Export Buttons */}
            <Button 
              variant="secondary" 
              size="sm" 
              className="h-8 gap-1.5 border-2"
              onClick={handleExportPdf}
            >
              <FileDown className="h-3.5 w-3.5" />
              <span className="text-xs">Export PDF</span>
            </Button>
            
            <Popover>
              <PopoverTrigger asChild>
                <Button variant="secondary" size="sm" className="h-8 gap-1.5 border-2">
                  <Filter className="h-3.5 w-3.5" />
                  <span className="text-xs">
                    {frequency === 'weekly' ? 'Weekly' : 'Monthly'}
                  </span>
                  {(dateFrom || dateTo || (deviceId && deviceId !== 'all')) && (
                    <span className="ml-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-medium text-primary-foreground">
                      •
                    </span>
                  )}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-80" align="end">
                <div className="space-y-4">
                  <div className="space-y-2">
                    <h4 className="font-medium leading-none">
                      {activeTab === 'users-devices' && 'User & Device Filters'}
                      {activeTab === 'crops-harvest-yield' && 'Crop & Harvest Filters'}
                      {activeTab === 'water-treatment' && 'Water Treatment Filters'}
                    </h4>
                    <p className="text-sm text-muted-foreground">
                      {activeTab === 'users-devices' && 'Filter user registration and login activity'}
                      {activeTab === 'crops-harvest-yield' && 'Filter harvest trends and yield data by device'}
                      {activeTab === 'water-treatment' && 'Filter treatment cycles and performance by device'}
                    </p>
                  </div>

                  <div className="space-y-3">
                    {/* Device Filter - Only for Crops & Harvest and Water Treatment */}
                    {(activeTab === 'crops-harvest-yield' || activeTab === 'water-treatment') && (
                      <div className="space-y-2">
                        <Label htmlFor="device">Device</Label>
                        <Select value={deviceId || 'all'} onValueChange={setDeviceId}>
                          <SelectTrigger id="device">
                            <SelectValue placeholder="All Devices" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="all">All Devices</SelectItem>
                            {devicesList.map((device) => (
                              <SelectItem key={device.id} value={device.id.toString()}>
                                {device.device_name} ({device.serial_number})
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                    )}

                    <div className="space-y-2">
                      <Label htmlFor="date-from">From Date</Label>
                      <input
                        id="date-from"
                        type="date"
                        value={dateFrom}
                        onChange={(e) => setDateFrom(e.target.value)}
                        className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="date-to">To Date</Label>
                      <input
                        id="date-to"
                        type="date"
                        value={dateTo}
                        onChange={(e) => setDateTo(e.target.value)}
                        className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                      />
                    </div>

                    {/* Frequency - Only show for Users & Devices and Water Treatment */}
                    {(activeTab === 'users-devices' || activeTab === 'water-treatment') && (
                      <div className="space-y-2">
                        <Label htmlFor="frequency">
                          {activeTab === 'users-devices' ? 'Registration Frequency' : 'Treatment Frequency'}
                        </Label>
                        <Select value={frequency} onValueChange={setFrequency}>
                          <SelectTrigger id="frequency">
                            <SelectValue placeholder="Select frequency" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="weekly">Weekly</SelectItem>
                            <SelectItem value="monthly">Monthly</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    )}

                    {/* Crops & Harvest specific: Harvest Frequency */}
                    {activeTab === 'crops-harvest-yield' && (
                      <div className="space-y-2">
                        <Label htmlFor="frequency">Harvest Period</Label>
                        <Select value={frequency} onValueChange={setFrequency}>
                          <SelectTrigger id="frequency">
                            <SelectValue placeholder="Select period" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="weekly">Weekly Harvest</SelectItem>
                            <SelectItem value="monthly">Monthly Harvest</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    )}

                    <div className="flex gap-2 pt-2">
                      <Button onClick={handleApplyFilters} className="flex-1">
                        Apply Filters
                      </Button>
                      <Button onClick={handleResetFilters} variant="secondary" className="flex-1">
                        Reset
                      </Button>
                    </div>
                  </div>
                </div>
              </PopoverContent>
            </Popover>
          </div>
        </div>

        {/* Tabs */}
        <Tabs defaultValue="users-devices" className="w-full" onValueChange={setActiveTab}>
          <TabsList className="h-12 p-1 bg-muted/60 rounded-xl">
            <TabsTrigger
              value="users-devices"
              className="h-10 px-4 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Users className="w-4 h-4 mr-2" />
              Users & Devices
            </TabsTrigger>
            <TabsTrigger
              value="crops-harvest-yield"
              className="h-10 px-4 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Leaf className="w-4 h-4 mr-2" />
              Crops and Harvest
            </TabsTrigger>
            <TabsTrigger
              value="water-treatment"
              className="h-10 px-4 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Waves className="w-4 h-4 mr-2" />
              Water Treatment
            </TabsTrigger>
          </TabsList>

          {/* Users & Devices Tab Content */}
          <TabsContent value="users-devices" className="mt-6">
            <div className="flex flex-col gap-6">
              {/* Summary Cards */}
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm text-muted-foreground">Total Users</p>
                        <p className="text-3xl font-bold text-foreground">{usersDevices.users.total}</p>
                        <p className="text-xs text-muted-foreground mt-1">
                          {usersDevices.users.active} active
                        </p>
                      </div>
                      <Users className="w-10 h-10 text-primary/70" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm text-muted-foreground">Total Devices</p>
                        <p className="text-3xl font-bold text-foreground">{usersDevices.devices.total}</p>
                        <p className="text-xs text-muted-foreground mt-1">
                          {usersDevices.devices.online} online
                        </p>
                      </div>
                      <Cast className="w-10 h-10 text-primary/70" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm text-muted-foreground">Inactive Users</p>
                        <p className="text-3xl font-bold text-foreground">{usersDevices.users.inactive}</p>
                        <p className="text-xs text-muted-foreground mt-1">
                          Need attention
                        </p>
                      </div>
                      <TrendingDown className="w-10 h-10 text-orange-500/70" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm text-muted-foreground">Without Devices</p>
                        <p className="text-3xl font-bold text-foreground">{usersDevices.users.without_devices}</p>
                        <p className="text-xs text-muted-foreground mt-1">
                          Users unassigned
                        </p>
                      </div>
                      <Activity className="w-10 h-10 text-red-500/70" />
                    </div>
                  </CardContent>
                </Card>
              </div>

              {/* Charts Row */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* User Registration Trend */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>User Registration Trend</CardTitle>
                    <CardDescription>
                      {frequency === 'weekly' 
                        ? 'New user registrations per week'
                        : 'New user registrations per month'}
                      {dateFrom && dateTo && ` (${dateFrom} to ${dateTo})`}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer
                      config={{
                        count: { label: 'Registrations', color: 'hsl(var(--primary))' }
                      }}
                      className="h-[300px] w-full"
                    >
                      <BarChart data={usersDevices.registration_trend}>
                        <CartesianGrid vertical={false} strokeDasharray="3 3" />
                        <XAxis
                          dataKey="month"
                          tickLine={false}
                          tickMargin={10}
                          axisLine={false}
                          tick={{ fontSize: 12 }}
                        />
                        <YAxis
                          tickLine={false}
                          axisLine={false}
                          tick={{ fontSize: 12 }}
                        />
                        <ChartTooltip cursor={false} content={<ChartTooltipContent />} />
                        <Bar dataKey="count" fill="hsl(var(--primary))" radius={8} />
                      </BarChart>
                    </ChartContainer>
                  </CardContent>
                </Card>

                {/* Login Activity Trend */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>Login Activity</CardTitle>
                    <CardDescription>
                      User login activity trends
                      {dateFrom && dateTo && ` from ${dateFrom} to ${dateTo}`}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer
                      config={{
                        unique_users: { label: 'Unique Users', color: 'hsl(var(--primary))' },
                        total_logins: { label: 'Total Logins', color: 'hsl(var(--chart-2))' }
                      }}
                      className="h-[300px] w-full"
                    >
                      <LineChart data={usersDevices.login_activity_trend}>
                        <CartesianGrid vertical={false} strokeDasharray="3 3" />
                        <XAxis
                          dataKey="date"
                          tickLine={false}
                          tickMargin={10}
                          axisLine={false}
                          tick={{ fontSize: 12 }}
                        />
                        <YAxis
                          tickLine={false}
                          axisLine={false}
                          tick={{ fontSize: 12 }}
                        />
                        <ChartTooltip cursor={false} content={<ChartTooltipContent />} />
                        <Line type="monotone" dataKey="unique_users" stroke="hsl(var(--primary))" strokeWidth={2} />
                        <Line type="monotone" dataKey="total_logins" stroke="hsl(var(--chart-2))" strokeWidth={2} />
                        <Legend 
                          formatter={(value) => {
                            if (value === 'unique_users') return 'Unique Users';
                            if (value === 'total_logins') return 'Total Logins';
                            return value;
                          }}
                        />
                      </LineChart>
                    </ChartContainer>
                  </CardContent>
                </Card>
              </div>

              {/* Device Status Distribution */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <CardTitle>Device Status Overview</CardTitle>
                  <CardDescription>Current status of all devices in the system</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="p-4 border rounded-lg">
                      <p className="text-sm text-muted-foreground">Online Devices</p>
                      <p className="text-2xl font-bold text-green-600">{usersDevices.devices.online}</p>
                      <p className="text-xs text-muted-foreground mt-1">
                        {usersDevices.devices.total > 0
                          ? `${Math.round((usersDevices.devices.online / usersDevices.devices.total) * 100)}%`
                          : '0%'} of total
                      </p>
                    </div>
                    <div className="p-4 border rounded-lg">
                      <p className="text-sm text-muted-foreground">Offline Devices</p>
                      <p className="text-2xl font-bold text-red-600">{usersDevices.devices.offline}</p>
                      <p className="text-xs text-muted-foreground mt-1">
                        {usersDevices.devices.total > 0
                          ? `${Math.round((usersDevices.devices.offline / usersDevices.devices.total) * 100)}%`
                          : '0%'} of total
                      </p>
                    </div>
                    <div className="p-4 border rounded-lg">
                      <p className="text-sm text-muted-foreground">Total Devices</p>
                      <p className="text-2xl font-bold text-foreground">{usersDevices.devices.total}</p>
                      <p className="text-xs text-muted-foreground mt-1">
                        Registered in system
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          {/* Crops, Harvest & Yields Tab Content */}
          <TabsContent value="crops-harvest-yield" className="mt-6">
            <div className="flex flex-col gap-6">
              {/* Summary Cards */}
              <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Harvest This Month</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvestYield.harvest_this_month} <span className="text-sm font-normal text-muted-foreground">crops</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Harvest This Year</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvestYield.harvest_this_year} <span className="text-sm font-normal text-muted-foreground">total</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Yield Weight</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvestYield.total_yield_weight} <span className="text-sm font-normal text-muted-foreground">kg</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Avg Yield/Setup</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvestYield.average_yield_per_setup} <span className="text-sm font-normal text-muted-foreground">kg</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Most Grown Crop</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvestYield.most_grown_crop || 'N/A'}</p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>
              </div>

              {/* Row 1: Combined Harvest & Yield Trends + Crop Type Distribution */}
              <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {/* Combined Harvest & Yield Trends */}
                <Card className="lg:col-span-3 rounded-2xl">
                  <CardHeader>
                    <CardTitle>Harvest & Yield Performance</CardTitle>
                    <CardDescription>
                      {frequency === 'weekly'
                        ? 'Weekly harvest count and yield weight'
                        : 'Monthly harvest count and yield weight'}
                      {dateFrom && dateTo && ` (${dateFrom} to ${dateTo})`}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer
                      config={{
                        harvested: { label: 'Crops Harvested', color: '#cadbb7' },
                        total_weight: { label: 'Yield Weight (kg)', color: 'hsl(var(--primary))' }
                      }}
                      className="h-[300px] w-full"
                    >
                      <BarChart data={cropsHarvestYield.monthly_harvest_trend} barCategoryGap="8%">
                        <CartesianGrid vertical={false} />
                        <XAxis
                          dataKey="month"
                          tickLine={false}
                          tickMargin={10}
                          axisLine={false}
                        />
                        <YAxis yAxisId="left" tickLine={false} axisLine={false} />
                        <YAxis yAxisId="right" orientation="right" tickLine={false} axisLine={false} />
                        <ChartTooltip
                          cursor={false}
                          content={<ChartTooltipContent />}
                        />
                        <Legend 
                          formatter={(value) => {
                            if (value === 'harvested') return 'Crops Harvested';
                            if (value === 'total_weight') return 'Yield Weight (kg)';
                            return value;
                          }}
                        />
                        <Bar
                          yAxisId="left"
                          dataKey="harvested"
                          fill="#cadbb7"
                          strokeWidth={2}
                          radius={20}
                          maxBarSize={60}
                        />
                        <Bar
                          yAxisId="right"
                          dataKey="total_weight"
                          fill="hsl(var(--primary))"
                          strokeWidth={2}
                          radius={20}
                          maxBarSize={60}
                        />
                      </BarChart>
                    </ChartContainer>
                  </CardContent>
                </Card>

                {/* Crop Type Distribution - Radar Chart */}
                <Card className="lg:col-span-2 rounded-2xl">
                  <CardHeader className="items-center pb-4">
                    <CardTitle>Crop Type Distribution</CardTitle>
                    <CardDescription>
                      Popular crops by harvest count
                      {dateFrom && dateTo && ` (filtered)`}
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="pb-0">
                    <ChartContainer
                      config={{
                        harvested: { label: 'Setups', color: 'hsl(142, 76%, 36%)' }
                      }}
                      className="mx-auto aspect-square max-h-[300px]"
                    >
                      <RadarChart data={cropsHarvestYield.crop_type_distribution}>
                        <ChartTooltip
                          cursor={false}
                          content={<ChartTooltipContent hideLabel />}
                        />
                        <PolarGrid className="fill-[hsl(142,76%,36%)] opacity-20" />
                        <PolarAngleAxis dataKey="crop" />
                        <Radar
                          dataKey="harvested"
                          fill="hsl(142, 76%, 36%)"
                          fillOpacity={0.5}
                        />
                      </RadarChart>
                    </ChartContainer>
                  </CardContent>
                </Card>
              </div>

              {/* Top Yielding Crops */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <CardTitle>Top Yielding Crops</CardTitle>
                  <CardDescription>
                    Crops ranked by total yield weight
                    {dateFrom && dateTo && ` within selected period`}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Crop Name</TableHead>
                        <TableHead>Total Weight (kg)</TableHead>
                        <TableHead>Setup Count</TableHead>
                        <TableHead>Avg per Setup</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {cropsHarvestYield.top_yielding_crops && cropsHarvestYield.top_yielding_crops.length > 0 ? (
                        cropsHarvestYield.top_yielding_crops.map((crop, index) => (
                          <TableRow key={index}>
                            <TableCell className="font-medium">{crop.crop_name}</TableCell>
                            <TableCell>{crop.total_weight} kg</TableCell>
                            <TableCell>{crop.setup_count}</TableCell>
                            <TableCell>{(crop.total_weight / crop.setup_count).toFixed(2)} kg</TableCell>
                          </TableRow>
                        ))
                      ) : (
                        <TableRow>
                          <TableCell colSpan={4} className="text-center text-muted-foreground">
                            No yield data available
                          </TableCell>
                        </TableRow>
                      )}
                    </TableBody>
                  </Table>
                </CardContent>
              </Card>

              {/* Grade Distribution */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <CardTitle>Yield Quality Distribution</CardTitle>
                  <CardDescription>Breakdown of harvested yield by grade quality</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {cropsHarvestYield.grade_distribution && Object.keys(cropsHarvestYield.grade_distribution).length > 0 ? (
                      Object.entries(cropsHarvestYield.grade_distribution).map(([grade, data]) => (
                        <div key={grade} className="p-4 border rounded-lg">
                          <p className="text-sm text-muted-foreground capitalize">{grade}</p>
                          <p className="text-2xl font-bold text-foreground">{data.weight} kg</p>
                          <p className="text-xs text-muted-foreground mt-1">
                            {data.count} units • {data.percentage}% of total
                          </p>
                        </div>
                      ))
                    ) : (
                      <div className="col-span-3 text-center text-muted-foreground p-4">
                        No grade distribution data available
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* Status Distributions */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {/* Growth Stage Distribution */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>Growth Stages</CardTitle>
                    <CardDescription>Distribution by growth stage</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-2">
                      {cropsHarvestYield.growth_stage_distribution && Object.keys(cropsHarvestYield.growth_stage_distribution).length > 0 ? (
                        Object.entries(cropsHarvestYield.growth_stage_distribution).map(([stage, count]) => (
                          <div key={stage} className="flex items-center justify-between p-2 border rounded">
                            <span className="text-sm capitalize">{stage}</span>
                            <span className="font-semibold">{count}</span>
                          </div>
                        ))
                      ) : (
                        <div className="text-center text-muted-foreground p-4">No data</div>
                      )}
                    </div>
                  </CardContent>
                </Card>

                {/* Health Status Distribution */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>Health Status</CardTitle>
                    <CardDescription>Distribution by health status</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-2">
                      {cropsHarvestYield.health_status_distribution && Object.keys(cropsHarvestYield.health_status_distribution).length > 0 ? (
                        Object.entries(cropsHarvestYield.health_status_distribution).map(([status, count]) => (
                          <div key={status} className="flex items-center justify-between p-2 border rounded">
                            <span className="text-sm capitalize">{status}</span>
                            <span className="font-semibold">{count}</span>
                          </div>
                        ))
                      ) : (
                        <div className="text-center text-muted-foreground p-4">No data</div>
                      )}
                    </div>
                  </CardContent>
                </Card>

                {/* Setup Status Distribution */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>Setup Status</CardTitle>
                    <CardDescription>Distribution by setup status</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-2">
                      {cropsHarvestYield.setups_by_status && Object.keys(cropsHarvestYield.setups_by_status).length > 0 ? (
                        Object.entries(cropsHarvestYield.setups_by_status).map(([status, count]) => (
                          <div key={status} className="flex items-center justify-between p-2 border rounded">
                            <span className="text-sm capitalize">{status}</span>
                            <span className="font-semibold">{count}</span>
                          </div>
                        ))
                      ) : (
                        <div className="text-center text-muted-foreground p-4">No data</div>
                      )}
                      <div className="mt-4 p-3 bg-primary/10 rounded-lg">
                        <p className="text-xs text-muted-foreground">Harvest Rate</p>
                        <p className="text-2xl font-bold text-primary">{cropsHarvestYield.harvest_rate}%</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </TabsContent>

          {/* Water Treatment Tab Content */}
          <TabsContent value="water-treatment" className="mt-6">
            <div className="flex flex-col gap-6">
              {/* Summary Cards */}
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Cycles</p>
                      <p className="text-3xl font-bold text-foreground">{waterTreatment.total_cycles}</p>
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Success Rate</p>
                      <p className="text-3xl font-bold text-green-600">{waterTreatment.success_rate}%</p>
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Failure Rate</p>
                      <p className="text-3xl font-bold text-red-600">{waterTreatment.failure_rate}%</p>
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Avg Duration</p>
                      <p className="text-3xl font-bold text-foreground">{waterTreatment.average_duration} <span className="text-lg font-normal text-muted-foreground">min</span></p>
                    </div>
                  </CardContent>
                </Card>
              </div>

              {/* Row 1: Filtration Cycle History */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Filtration Stats Cards */}
                <FiltrationCyclesCard />

                {/* Weekly Filtration Chart */}
                <Card className="lg:col-span-2 rounded-2xl">
                  <CardHeader>
                    <div className="flex items-center justify-between">
                      <div>
                        <CardTitle>Water Filtered</CardTitle>
                        <CardDescription>
                          {frequency === 'weekly'
                            ? 'Total liters filtered per week'
                            : 'Total liters filtered per month'}
                          {dateFrom && dateTo && ` (${dateFrom} to ${dateTo})`}
                        </CardDescription>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent className="pt-0">
                    <ChartContainer
                      config={{
                        filtered: { label: 'Water Filtered (L)', color: '#60A5FA' }
                      }}
                      className="h-[240px] w-full aspect-auto"
                    >
                      <BarChart data={waterTreatment.weekly_filtration} barCategoryGap="10%">
                        <CartesianGrid vertical={false} strokeDasharray="3 3" />
                        <XAxis
                          dataKey="week"
                          tickLine={false}
                          tickMargin={10}
                          axisLine={false}
                          tick={{ fontSize: 12 }}
                        />
                        <YAxis
                          tickLine={false}
                          axisLine={false}
                          tick={{ fontSize: 12 }}
                          tickFormatter={(value) => `${value}L`}
                        />
                        <ChartTooltip
                          cursor={false}
                          content={<ChartTooltipContent />}
                        />
                        <Bar
                          dataKey="filtered"
                          fill="#60A5FA"
                          radius={12}
                          barSize={60}
                        />
                      </BarChart>
                    </ChartContainer>
                  </CardContent>
                </Card>
              </div>

              {/* Treatment Trends */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <CardTitle>Treatment Trends</CardTitle>
                  <CardDescription>
                    Treatment cycles and success rate
                    {dateFrom && dateTo && ` from ${dateFrom} to ${dateTo}`}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ChartContainer
                    config={{
                      cycle_count: { label: 'Total Cycles', color: 'hsl(var(--primary))' },
                      success_count: { label: 'Successful', color: 'hsl(var(--chart-2))' }
                    }}
                    className="h-[300px] w-full"
                  >
                    <LineChart data={waterTreatment.treatment_trends}>
                      <CartesianGrid vertical={false} strokeDasharray="3 3" />
                      <XAxis
                        dataKey="date"
                        tickLine={false}
                        tickMargin={10}
                        axisLine={false}
                        tick={{ fontSize: 12 }}
                      />
                      <YAxis
                        tickLine={false}
                        axisLine={false}
                        tick={{ fontSize: 12 }}
                      />
                      <ChartTooltip cursor={false} content={<ChartTooltipContent />} />
                      <Line type="monotone" dataKey="cycle_count" stroke="hsl(var(--primary))" strokeWidth={2} />
                      <Line type="monotone" dataKey="success_count" stroke="hsl(var(--chart-2))" strokeWidth={2} />
                      <Legend 
                        formatter={(value) => {
                          if (value === 'cycle_count') return 'Total Cycles';
                          if (value === 'success_count') return 'Successful';
                          return value;
                        }}
                      />
                    </LineChart>
                  </ChartContainer>
                </CardContent>
              </Card>

              {/* Stage Performance */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <CardTitle>Stage Performance Breakdown</CardTitle>
                  <CardDescription>Performance metrics for each treatment stage</CardDescription>
                </CardHeader>
                <CardContent>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Stage Name</TableHead>
                        <TableHead>Total Count</TableHead>
                        <TableHead>Pass Rate</TableHead>
                        <TableHead>Avg pH</TableHead>
                        <TableHead>Avg Turbidity</TableHead>
                        <TableHead>Avg TDS</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {waterTreatment.stage_performance && waterTreatment.stage_performance.length > 0 ? (
                        waterTreatment.stage_performance.map((stage, index) => (
                          <TableRow key={index}>
                            <TableCell className="font-medium">{stage.stage_name}</TableCell>
                            <TableCell>{stage.total_count}</TableCell>
                            <TableCell>
                              <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                stage.pass_rate >= 80
                                  ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                  : stage.pass_rate >= 60
                                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                  : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                              }`}>
                                {stage.pass_rate}%
                              </span>
                            </TableCell>
                            <TableCell>{stage.avg_ph ?? 'N/A'}</TableCell>
                            <TableCell>{stage.avg_turbidity ?? 'N/A'}</TableCell>
                            <TableCell>{stage.avg_tds ?? 'N/A'}</TableCell>
                          </TableRow>
                        ))
                      ) : (
                        <TableRow>
                          <TableCell colSpan={6} className="text-center text-muted-foreground">
                            No stage performance data available
                          </TableCell>
                        </TableRow>
                      )}
                    </TableBody>
                  </Table>
                </CardContent>
              </Card>
            </div>
          </TabsContent>
        </Tabs>

      </div>
    </AppLayout>
  )
}
