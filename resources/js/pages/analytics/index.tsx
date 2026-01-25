import AppLayout from '@/layouts/app-layout'
import { Head, usePage } from '@inertiajs/react'
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart'
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, RadarChart, PolarAngleAxis, PolarGrid, Radar, LineChart, Line, Legend } from 'recharts'
import { Users, Cast, Leaf, Waves, TrendingUp, TrendingDown, Activity } from 'lucide-react'
import {
  TextureCardContent,
  TextureCardStyled
} from '@/components/ui/texture-card'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { FiltrationCyclesCard } from '@/components/filtration-cycles-card'
import { UsersDevicesAnalytics, CropsHarvestAnalytics, YieldAnalytics, WaterTreatmentAnalytics } from '@/types/analytics'

export default function Analytics() {
  const { usersDevices, cropsHarvest, yields, waterTreatment } = usePage<{
    usersDevices: UsersDevicesAnalytics;
    cropsHarvest: CropsHarvestAnalytics;
    yields: YieldAnalytics;
    waterTreatment: WaterTreatmentAnalytics;
  }>().props;

  return (
    <AppLayout title="">
      <Head title="Analytics" />
      <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">

        {/* Header */}
        <div>
          <h1 className="text-2xl font-bold text-foreground">Analytics Overview</h1>
          <p className="text-sm text-muted-foreground mt-1">Track your system performance and insights</p>
        </div>

        {/* Tabs */}
        <Tabs defaultValue="users-devices" className="w-full">
          <TabsList className="h-12 p-1 bg-muted/60 rounded-xl">
            <TabsTrigger
              value="users-devices"
              className="h-10 px-4 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Users className="w-4 h-4 mr-2" />
              Users & Devices
            </TabsTrigger>
            <TabsTrigger
              value="crops-harvest"
              className="h-10 px-4 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Leaf className="w-4 h-4 mr-2" />
              Crops & Harvest
            </TabsTrigger>
            <TabsTrigger
              value="yields"
              className="h-10 px-4 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <TrendingUp className="w-4 h-4 mr-2" />
              Yields
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
                    <CardDescription>New user registrations over the last 12 months</CardDescription>
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
                    <CardDescription>Daily login activity for the last 30 days</CardDescription>
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
                        <Legend />
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

          {/* Crops & Harvest Tab Content */}
          <TabsContent value="crops-harvest" className="mt-6">
            <div className="flex flex-col gap-6">
              {/* Summary Cards */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Harvest This Month</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvest.harvest_this_month} <span className="text-sm font-normal text-muted-foreground">crops</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Harvest This Year</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvest.harvest_this_year} <span className="text-sm font-normal text-muted-foreground">total</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Most Grown Crop Type</p>
                      <p className="text-2xl font-bold text-foreground">{cropsHarvest.most_grown_crop || 'N/A'}</p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>
              </div>

              {/* Row 1: Harvest Bar Chart + Crop Type Distribution Radar */}
              <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {/* Harvest Bar Chart */}
                <Card className="lg:col-span-3 rounded-2xl">
                  <CardHeader>
                    <CardTitle>Monthly Harvest Insights</CardTitle>
                    <CardDescription>Harvest trends over the last 12 months</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer
                      config={{
                        harvested: { label: 'Harvested', color: '#cadbb7' }
                      }}
                      className="h-[300px] w-full"
                    >
                      <BarChart data={cropsHarvest.monthly_harvest_trend} barCategoryGap="8%">
                        <CartesianGrid vertical={false} />
                        <XAxis
                          dataKey="month"
                          tickLine={false}
                          tickMargin={10}
                          axisLine={false}
                        />
                        <ChartTooltip
                          cursor={false}
                          content={<ChartTooltipContent hideLabel />}
                        />
                        <Bar
                          dataKey="harvested"
                          fill="#cadbb7"
                          strokeWidth={2}
                          radius={20}
                          maxBarSize={80}
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
                      Top crops by setup count
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="pb-0">
                    <ChartContainer
                      config={{
                        harvested: { label: 'Setups', color: 'hsl(142, 76%, 36%)' }
                      }}
                      className="mx-auto aspect-square max-h-[300px]"
                    >
                      <RadarChart data={cropsHarvest.crop_type_distribution}>
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
                      {Object.entries(cropsHarvest.growth_stage_distribution).map(([stage, count]) => (
                        <div key={stage} className="flex items-center justify-between p-2 border rounded">
                          <span className="text-sm capitalize">{stage}</span>
                          <span className="font-semibold">{count}</span>
                        </div>
                      ))}
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
                      {Object.entries(cropsHarvest.health_status_distribution).map(([status, count]) => (
                        <div key={status} className="flex items-center justify-between p-2 border rounded">
                          <span className="text-sm capitalize">{status}</span>
                          <span className="font-semibold">{count}</span>
                        </div>
                      ))}
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
                      {Object.entries(cropsHarvest.setups_by_status).map(([status, count]) => (
                        <div key={status} className="flex items-center justify-between p-2 border rounded">
                          <span className="text-sm capitalize">{status}</span>
                          <span className="font-semibold">{count}</span>
                        </div>
                      ))}
                      <div className="mt-4 p-3 bg-primary/10 rounded-lg">
                        <p className="text-xs text-muted-foreground">Harvest Rate</p>
                        <p className="text-2xl font-bold text-primary">{cropsHarvest.harvest_rate}%</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </TabsContent>

          {/* Yields Tab Content */}
          <TabsContent value="yields" className="mt-6">
            <div className="flex flex-col gap-6">
              {/* Summary Cards */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Yield Weight</p>
                      <p className="text-3xl font-bold text-foreground">{yields.total_yield_weight} <span className="text-lg font-normal text-muted-foreground">kg</span></p>
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Yield Count</p>
                      <p className="text-3xl font-bold text-foreground">{yields.total_yield_count} <span className="text-lg font-normal text-muted-foreground">units</span></p>
                    </div>
                  </CardContent>
                </Card>

                <Card className="rounded-2xl">
                  <CardContent className="pt-6">
                    <div>
                      <p className="text-sm text-muted-foreground">Avg Yield Per Setup</p>
                      <p className="text-3xl font-bold text-foreground">{yields.average_yield_per_setup} <span className="text-lg font-normal text-muted-foreground">kg</span></p>
                    </div>
                  </CardContent>
                </Card>
              </div>

              {/* Charts Row */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Yield Trends */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>Yield Trends</CardTitle>
                    <CardDescription>Total yield weight over the last 12 months</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer
                      config={{
                        total_weight: { label: 'Weight (kg)', color: 'hsl(var(--primary))' }
                      }}
                      className="h-[300px] w-full"
                    >
                      <LineChart data={yields.yield_trends}>
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
                        <Line type="monotone" dataKey="total_weight" stroke="hsl(var(--primary))" strokeWidth={2} />
                      </LineChart>
                    </ChartContainer>
                  </CardContent>
                </Card>

                {/* Top Yielding Crops */}
                <Card className="rounded-2xl">
                  <CardHeader>
                    <CardTitle>Top Yielding Crops</CardTitle>
                    <CardDescription>Crops with highest total yield</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Crop Name</TableHead>
                          <TableHead>Weight (kg)</TableHead>
                          <TableHead>Setups</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {yields.top_yielding_crops.map((crop, index) => (
                          <TableRow key={index}>
                            <TableCell className="font-medium">{crop.crop_name}</TableCell>
                            <TableCell>{crop.total_weight}</TableCell>
                            <TableCell>{crop.setup_count}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </CardContent>
                </Card>
              </div>

              {/* Grade Distribution */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <CardTitle>Grade Distribution</CardTitle>
                  <CardDescription>Breakdown of yield by grade quality</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {Object.entries(yields.grade_distribution).map(([grade, data]) => (
                      <div key={grade} className="p-4 border rounded-lg">
                        <p className="text-sm text-muted-foreground capitalize">{grade}</p>
                        <p className="text-2xl font-bold text-foreground">{data.weight} kg</p>
                        <p className="text-xs text-muted-foreground mt-1">
                          {data.count} units • {data.percentage}%
                        </p>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
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
                        <CardDescription>Total liters filtered per week</CardDescription>
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
                  <CardDescription>Daily treatment cycles and success rate (last 30 days)</CardDescription>
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
                      <Legend />
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
                      {waterTreatment.stage_performance.map((stage, index) => (
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
                      ))}
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
