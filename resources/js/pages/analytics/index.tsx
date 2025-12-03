import AppLayout from '@/layouts/app-layout'
import { Head } from '@inertiajs/react'
import { useState, useMemo } from 'react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, ReferenceArea, ResponsiveContainer } from 'recharts'

type DateRange = 'today' | '24h' | '7d' | '30d' | 'custom'

// Mock data generator for realistic sensor readings
const generateMockData = (hours: number) => {
  const data = []
  const now = new Date()

  for (let i = hours; i >= 0; i--) {
    const time = new Date(now.getTime() - i * 60 * 60 * 1000)

    // Generate realistic pH values (5.5-6.5 is optimal for hydroponics)
    const baselinePh = 6.0
    const phVariation = (Math.random() - 0.5) * 0.8
    const ph = +(baselinePh + phVariation).toFixed(2)

    // Generate realistic TDS values (800-1500 ppm is typical)
    const baselineTds = 1150
    const tdsVariation = (Math.random() - 0.5) * 400
    const tds = Math.round(baselineTds + tdsVariation)

    data.push({
      time: time.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        ...(hours > 24 ? { month: 'short', day: 'numeric' } : {})
      }),
      timestamp: time.getTime(),
      ph,
      tds
    })
  }

  return data
}

export default function Analytics() {
  const [selectedRange, setSelectedRange] = useState<DateRange>('24h')

  const sensorData = useMemo(() => {
    switch (selectedRange) {
      case 'today':
        return generateMockData(24)
      case '24h':
        return generateMockData(24)
      case '7d':
        return generateMockData(168) // 7 days
      case '30d':
        return generateMockData(720) // 30 days
      case 'custom':
        return generateMockData(24) // Default to 24h for custom
      default:
        return generateMockData(24)
    }
  }, [selectedRange])

  const phChartConfig = {
    ph: {
      label: "pH Level",
      color: "hsl(var(--chart-1))",
    },
  }

  const tdsChartConfig = {
    tds: {
      label: "TDS (ppm)",
      color: "hsl(var(--chart-2))",
    },
  }

  return (
    <AppLayout title="">
      <Head title="Analytics" />
      <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

        {/* Header */}
        <div>
          <h1 className="text-2xl font-bold">Sensor Data Analytics</h1>
          <p className="text-muted-foreground">Historical charts and performance data of all sensor readings.</p>
        </div>

        {/* Date Filter Row */}
        <div className="flex flex-wrap items-center gap-2">
          <Button
            variant={selectedRange === 'today' ? 'default' : 'outline'}
            onClick={() => setSelectedRange('today')}
          >
            Today
          </Button>
          <Button
            variant={selectedRange === '24h' ? 'default' : 'outline'}
            onClick={() => setSelectedRange('24h')}
          >
            Last 24 Hours
          </Button>
          <Button
            variant={selectedRange === '7d' ? 'default' : 'outline'}
            onClick={() => setSelectedRange('7d')}
          >
            Last 7 Days
          </Button>
          <Button
            variant={selectedRange === '30d' ? 'default' : 'outline'}
            onClick={() => setSelectedRange('30d')}
          >
            Last 30 Days
          </Button>
          <Button
            variant={selectedRange === 'custom' ? 'default' : 'outline'}
            onClick={() => setSelectedRange('custom')}
          >
            Custom Range
          </Button>
        </div>

        {/* pH Level Graph */}
        <Card>
          <CardHeader>
            <CardTitle>pH Level</CardTitle>
            <CardDescription>
              pH readings over selected time range (Optimal: 5.5-6.5)
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ChartContainer config={phChartConfig} className="h-[400px] w-full">
              <LineChart data={sensorData} margin={{ top: 5, right: 30, left: 0, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />

                {/* Safe pH range background */}
                <ReferenceArea
                  y1={5.5}
                  y2={6.5}
                  fill="hsl(var(--chart-1))"
                  fillOpacity={0.1}
                  label={{ value: 'Optimal Range', position: 'insideTopRight', fill: 'hsl(var(--muted-foreground))' }}
                />

                <XAxis
                  dataKey="time"
                  tick={{ fontSize: 12 }}
                  angle={-45}
                  textAnchor="end"
                  height={80}
                />
                <YAxis
                  domain={[4, 8]}
                  tick={{ fontSize: 12 }}
                  label={{ value: 'pH', angle: -90, position: 'insideLeft' }}
                />
                <ChartTooltip content={<ChartTooltipContent />} />
                <Line
                  type="monotone"
                  dataKey="ph"
                  stroke="var(--color-ph)"
                  strokeWidth={2}
                  dot={{ r: 3 }}
                  activeDot={{ r: 5 }}
                />
              </LineChart>
            </ChartContainer>
          </CardContent>
        </Card>

        {/* TDS (ppm) Graph */}
        <Card>
          <CardHeader>
            <CardTitle>TDS (ppm)</CardTitle>
            <CardDescription>
              Nutrient concentration over selected time range (Optimal: 800-1500 ppm)
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ChartContainer config={tdsChartConfig} className="h-[400px] w-full">
              <LineChart data={sensorData} margin={{ top: 5, right: 30, left: 0, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />

                {/* Low range (below optimal) */}
                <ReferenceArea
                  y1={0}
                  y2={800}
                  fill="hsl(var(--destructive))"
                  fillOpacity={0.05}
                />

                {/* Safe TDS range */}
                <ReferenceArea
                  y1={800}
                  y2={1500}
                  fill="hsl(var(--chart-2))"
                  fillOpacity={0.1}
                  label={{ value: 'Optimal Range', position: 'insideTopRight', fill: 'hsl(var(--muted-foreground))' }}
                />

                {/* High range (above optimal) */}
                <ReferenceArea
                  y1={1500}
                  y2={2000}
                  fill="hsl(var(--destructive))"
                  fillOpacity={0.05}
                />

                <XAxis
                  dataKey="time"
                  tick={{ fontSize: 12 }}
                  angle={-45}
                  textAnchor="end"
                  height={80}
                />
                <YAxis
                  domain={[0, 2000]}
                  tick={{ fontSize: 12 }}
                  label={{ value: 'ppm', angle: -90, position: 'insideLeft' }}
                />
                <ChartTooltip content={<ChartTooltipContent />} />
                <Line
                  type="monotone"
                  dataKey="tds"
                  stroke="var(--color-tds)"
                  strokeWidth={2}
                  dot={{ r: 3 }}
                  activeDot={{ r: 5 }}
                />
              </LineChart>
            </ChartContainer>
          </CardContent>
        </Card>

      </div>
    </AppLayout>
  )
}

