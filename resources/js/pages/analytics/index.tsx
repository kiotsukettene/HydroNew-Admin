import AppLayout from '@/layouts/app-layout'
import { Head } from '@inertiajs/react'
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart'
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, RadarChart, PolarAngleAxis, PolarGrid, Radar, Rectangle } from 'recharts'
import { TrendingUp, Leaf, Waves, Sparkles } from 'lucide-react'
import {
  TextureCardContent,
  TextureCardStyled
} from '@/components/ui/texture-card'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { FiltrationCyclesCard } from '@/components/filtration-cycles-card'

// Monthly harvest data with fill colors
const harvestData = [
  { month: 'Jan', harvested: 28, fill: 'var(--color-jan)' },
  { month: 'Feb', harvested: 35, fill: 'var(--color-feb)' },
  { month: 'Mar', harvested: 42, fill: 'var(--color-mar)' },
  { month: 'Apr', harvested: 38, fill: 'var(--color-apr)' },
  { month: 'May', harvested: 52, fill: 'var(--color-may)' },
  { month: 'Jun', harvested: 48, fill: 'var(--color-jun)' },
  { month: 'Jul', harvested: 55, fill: 'var(--color-jul)' },
  { month: 'Aug', harvested: 45, fill: 'var(--color-aug)' },
  { month: 'Sep', harvested: 40, fill: 'var(--color-sep)' },
  { month: 'Oct', harvested: 35, fill: 'var(--color-oct)' },
  { month: 'Nov', harvested: 30, fill: 'var(--color-nov)' },
  { month: 'Dec', harvested: 25, fill: 'var(--color-dec)' }
]

// Chart config for harvest data
const harvestChartConfig = {
  harvested: { label: 'Harvested' },
  jan: { label: 'Jan', color: '#cadbb7' },
  feb: { label: 'Feb', color: '#cadbb7' },
  mar: { label: 'Mar', color: '#cadbb7' },
  apr: { label: 'Apr', color: '#cadbb7' },
  may: { label: 'May', color: '#cadbb7' },
  jun: { label: 'Jun', color: '#cadbb7' },
  jul: { label: 'Jul', color: '#cadbb7' },
  aug: { label: 'Aug', color: '#cadbb7' },
  sep: { label: 'Sep', color: '#cadbb7' },
  oct: { label: 'Oct', color: '#cadbb7' },
  nov: { label: 'Nov', color: '#cadbb7' },
  dec: { label: 'Dec', color: '#cadbb7' }
}

// Crop Type Radar Data
const cropRadarData = [
  { crop: 'Romaine', harvested: 112 },
  { crop: 'Basil', harvested: 80 },
  { crop: 'Iceberg', harvested: 64 },
  { crop: 'Kale', harvested: 38 },
  { crop: 'Spinach', harvested: 24 }
]

const cropRadarConfig = {
  harvested: {
    label: 'Harvested',
    color: 'hsl(142, 76%, 36%)'
  }
}

// Filtration History Data (weekly)
const filtrationData = [
  { week: 'Week 1', filtered: 120, cycles: 8 },
  { week: 'Week 2', filtered: 145, cycles: 10 },
  { week: 'Week 3', filtered: 98, cycles: 7 },
  { week: 'Week 4', filtered: 167, cycles: 12 }
]

const filtrationChartConfig = {
  filtered: { label: 'Water Filtered (L)', color: '#60A5FA' }
}

// Clean vs Dirty Water Comparison Data
const waterComparisonData = [
  { month: 'Jan', dirty: 85, clean: 12, improvement: 86 },
  { month: 'Feb', dirty: 92, clean: 10, improvement: 89 },
  { month: 'Mar', dirty: 78, clean: 8, improvement: 90 },
  { month: 'Apr', dirty: 88, clean: 11, improvement: 87 },
  { month: 'May', dirty: 95, clean: 9, improvement: 91 },
  { month: 'Jun', dirty: 82, clean: 7, improvement: 91 }
]

const waterComparisonConfig = {
  dirty: { label: 'Dirty Water (NTU)', color: '#F97316' },
  clean: { label: 'Clean Water (NTU)', color: '#22C55E' }
}

export default function Analytics() {
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
        <Tabs defaultValue="water-quality" className="w-full">
          <TabsList className="h-12 p-1 bg-muted/60 rounded-xl">
            <TabsTrigger
              value="water-quality"
              className="h-10 px-6 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Waves className="w-4 h-4 mr-2" />
              Water Quality
            </TabsTrigger>
            <TabsTrigger
              value="crops-harvest"
              className="h-10 px-6 text-sm font-medium rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md"
            >
              <Leaf className="w-4 h-4 mr-2" />
              Crops & Harvest
            </TabsTrigger>
          </TabsList>

          {/* Water Quality Tab Content */}
          <TabsContent value="water-quality" className="mt-6">
            <div className="flex flex-col gap-6">
              {/* Row 1: Filtration Cycle History */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Filtration Stats Cards */}
                <FiltrationCyclesCard />

                {/* Filtration Bar Chart */}
                <Card className="lg:col-span-2 rounded-2xl">
                  <CardHeader>
                    <CardTitle>Water Filtered Per Week</CardTitle>
                    <CardDescription>Total liters filtered this month</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer config={filtrationChartConfig} className="h-[200px]">
                      <BarChart data={filtrationData} barCategoryGap="20%">
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
                          maxBarSize={60}
                        />
                      </BarChart>
                    </ChartContainer>
                  </CardContent>
                </Card>
              </div>

              {/* Row 2: Clean vs Dirty Water Comparison */}
              <Card className="rounded-2xl">
                <CardHeader>
                  <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                      <CardTitle className="flex items-center gap-2">
                        <Sparkles className="w-5 h-5 text-emerald-500" />
                        Clean vs Dirty Water Comparison
                      </CardTitle>
                      <CardDescription>Monthly turbidity levels (NTU) before and after filtration</CardDescription>
                    </div>
                    <div className="flex items-center gap-4 text-sm">
                      <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded-full bg-orange-500" />
                        <span className="text-muted-foreground">Dirty Water</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded-full bg-emerald-500" />
                        <span className="text-muted-foreground">Clean Water</span>
                      </div>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <ChartContainer config={waterComparisonConfig} className="h-[280px]">
                    <BarChart data={waterComparisonData} barCategoryGap="25%">
                      <CartesianGrid vertical={false} strokeDasharray="3 3" />
                      <XAxis
                        dataKey="month"
                        tickLine={false}
                        tickMargin={10}
                        axisLine={false}
                      />
                      <YAxis
                        tickLine={false}
                        axisLine={false}
                        tickFormatter={(value) => `${value}`}
                        label={{ value: 'NTU', angle: -90, position: 'insideLeft', style: { textAnchor: 'middle' } }}
                      />
                      <ChartTooltip
                        cursor={false}
                        content={<ChartTooltipContent />}
                      />
                      <Bar dataKey="dirty" fill="#F97316" radius={8} maxBarSize={35} name="Dirty Water" />
                      <Bar dataKey="clean" fill="#22C55E" radius={8} maxBarSize={35} name="Clean Water" />
                    </BarChart>
                  </ChartContainer>

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
                      <p className="text-2xl font-bold text-foreground">42 <span className="text-sm font-normal text-muted-foreground">crops</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Total Harvest This Year</p>
                      <p className="text-2xl font-bold text-foreground">318 <span className="text-sm font-normal text-muted-foreground">total</span></p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>

                <TextureCardStyled className="rounded-2xl">
                  <TextureCardContent className="flex items-center gap-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Most Grown Crop Type</p>
                      <p className="text-2xl font-bold text-foreground">Romaine</p>
                    </div>
                  </TextureCardContent>
                </TextureCardStyled>
              </div>

              {/* Row 1: Harvest Bar Chart + Crop Type Distribution Radar */}
              <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {/* Harvest Bar Chart */}
                <Card className="lg:col-span-3 rounded-2xl">
                  <CardHeader>
                    <CardTitle>Harvest Insights</CardTitle>
                    <CardDescription>January - December 2025</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ChartContainer config={harvestChartConfig}>
                      <BarChart accessibilityLayer data={harvestData} barCategoryGap="8%">
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
                          strokeWidth={2}
                          radius={20}
                          maxBarSize={80}
                          activeIndex={6}
                          activeBar={({ ...props }) => {
                            return (
                              <Rectangle
                                {...props}
                                fillOpacity={0.8}
                                stroke={props.payload.fill}
                                strokeDasharray={4}
                                strokeDashoffset={4}
                              />
                            )
                          }}
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
                      Harvested crops by type
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="pb-0">
                    <ChartContainer
                      config={cropRadarConfig}
                      className="mx-auto aspect-square max-h-[350px]"
                    >
                      <RadarChart data={cropRadarData}>
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
            </div>
          </TabsContent>
        </Tabs>

      </div>
    </AppLayout>
  )
}
