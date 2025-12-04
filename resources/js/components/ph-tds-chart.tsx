"use client"

import * as React from "react"
import { Area, AreaChart, CartesianGrid, XAxis } from "recharts"

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { cn } from "@/lib/utils"
import {
  ChartConfig,
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  ChartTooltipContent,
} from "@/components/ui/chart"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

export const description = "An interactive area chart"

const chartData = [
  { date: "2024-04-01", pH: 7.2, TDS: 35 },
  { date: "2024-04-02", pH: 6.8, TDS: 42 },
  { date: "2024-04-03", pH: 7.5, TDS: 28 },
  { date: "2024-04-04", pH: 7.8, TDS: 38 },
  { date: "2024-04-05", pH: 8.2, TDS: 45 },
  { date: "2024-04-06", pH: 7.9, TDS: 48 },
  { date: "2024-04-07", pH: 7.6, TDS: 32 },
  { date: "2024-04-08", pH: 8.5, TDS: 46 },
  { date: "2024-04-09", pH: 6.5, TDS: 22 },
  { date: "2024-04-10", pH: 7.7, TDS: 34 },
  { date: "2024-04-11", pH: 8.1, TDS: 49 },
  { date: "2024-04-12", pH: 7.4, TDS: 36 },
  { date: "2024-04-13", pH: 8.3, TDS: 50 },
  { date: "2024-04-14", pH: 7.1, TDS: 40 },
  { date: "2024-04-15", pH: 6.9, TDS: 30 },
  { date: "2024-04-16", pH: 7.0, TDS: 34 },
  { date: "2024-04-17", pH: 9.2, TDS: 48 },
  { date: "2024-04-18", pH: 8.4, TDS: 50 },
  { date: "2024-04-19", pH: 7.6, TDS: 32 },
  { date: "2024-04-20", pH: 6.7, TDS: 28 },
  { date: "2024-04-21", pH: 7.1, TDS: 35 },
  { date: "2024-04-22", pH: 7.5, TDS: 30 },
  { date: "2024-04-23", pH: 7.0, TDS: 39 },
  { date: "2024-04-24", pH: 8.6, TDS: 45 },
  { date: "2024-04-25", pH: 7.4, TDS: 42 },
  { date: "2024-04-26", pH: 6.4, TDS: 25 },
  { date: "2024-04-27", pH: 8.5, TDS: 50 },
  { date: "2024-04-28", pH: 6.9, TDS: 32 },
  { date: "2024-04-29", pH: 8.0, TDS: 41 },
  { date: "2024-04-30", pH: 9.5, TDS: 49 },
  { date: "2024-05-01", pH: 7.3, TDS: 38 },
  { date: "2024-05-02", pH: 7.8, TDS: 46 },
  { date: "2024-05-03", pH: 7.6, TDS: 33 },
  { date: "2024-05-04", pH: 8.5, TDS: 50 },
  { date: "2024-05-05", pH: 10.2, TDS: 48 },
  { date: "2024-05-06", pH: 10.5, TDS: 50 },
  { date: "2024-05-07", pH: 8.6, TDS: 44 },
  { date: "2024-05-08", pH: 7.2, TDS: 36 },
  { date: "2024-05-09", pH: 7.5, TDS: 32 },
  { date: "2024-05-10", pH: 7.8, TDS: 47 },
  { date: "2024-05-11", pH: 8.2, TDS: 43 },
  { date: "2024-05-12", pH: 7.4, TDS: 41 },
  { date: "2024-05-13", pH: 7.4, TDS: 29 },
  { date: "2024-05-14", pH: 9.3, TDS: 50 },
  { date: "2024-05-15", pH: 10.0, TDS: 49 },
  { date: "2024-05-16", pH: 8.3, TDS: 50 },
  { date: "2024-05-17", pH: 10.6, TDS: 50 },
  { date: "2024-05-18", pH: 8.0, TDS: 48 },
  { date: "2024-05-19", pH: 7.5, TDS: 32 },
  { date: "2024-05-20", pH: 7.3, TDS: 39 },
  { date: "2024-05-21", pH: 6.6, TDS: 26 },
  { date: "2024-05-22", pH: 6.5, TDS: 23 },
  { date: "2024-05-23", pH: 7.7, TDS: 45 },
  { date: "2024-05-24", pH: 7.8, TDS: 38 },
  { date: "2024-05-25", pH: 7.4, TDS: 42 },
  { date: "2024-05-26", pH: 7.5, TDS: 30 },
  { date: "2024-05-27", pH: 9.0, TDS: 50 },
  { date: "2024-05-28", pH: 7.5, TDS: 33 },
  { date: "2024-05-29", pH: 6.4, TDS: 25 },
  { date: "2024-05-30", pH: 8.2, TDS: 44 },
  { date: "2024-05-31", pH: 7.3, TDS: 39 },
  { date: "2024-06-01", pH: 7.3, TDS: 35 },
  { date: "2024-06-02", pH: 9.8, TDS: 50 },
  { date: "2024-06-03", pH: 6.9, TDS: 29 },
  { date: "2024-06-04", pH: 9.1, TDS: 49 },
  { date: "2024-06-05", pH: 6.7, TDS: 26 },
  { date: "2024-06-06", pH: 7.8, TDS: 42 },
  { date: "2024-06-07", pH: 8.1, TDS: 49 },
  { date: "2024-06-08", pH: 8.5, TDS: 46 },
  { date: "2024-06-09", pH: 9.1, TDS: 50 },
  { date: "2024-06-10", pH: 7.2, TDS: 35 },
  { date: "2024-06-11", pH: 6.8, TDS: 28 },
  { date: "2024-06-12", pH: 10.3, TDS: 50 },
  { date: "2024-06-13", pH: 6.5, TDS: 25 },
  { date: "2024-06-14", pH: 9.0, TDS: 49 },
  { date: "2024-06-15", pH: 8.0, TDS: 48 },
  { date: "2024-06-16", pH: 8.4, TDS: 46 },
  { date: "2024-06-17", pH: 10.0, TDS: 50 },
  { date: "2024-06-18", pH: 6.9, TDS: 30 },
  { date: "2024-06-19", pH: 8.2, TDS: 45 },
  { date: "2024-06-20", pH: 8.8, TDS: 50 },
  { date: "2024-06-21", pH: 7.3, TDS: 36 },
  { date: "2024-06-22", pH: 8.1, TDS: 43 },
  { date: "2024-06-23", pH: 10.1, TDS: 50 },
  { date: "2024-06-24", pH: 7.0, TDS: 32 },
  { date: "2024-06-25", pH: 7.1, TDS: 33 },
  { date: "2024-06-26", pH: 9.0, TDS: 49 },
  { date: "2024-06-27", pH: 9.3, TDS: 50 },
  { date: "2024-06-28", pH: 7.2, TDS: 35 },
  { date: "2024-06-29", pH: 6.9, TDS: 29 },
  { date: "2024-06-30", pH: 9.2, TDS: 50 },
]

const chartConfig = {
  visitors: {
    label: "Visitors",
  },
  pH: {
    label: "pH",
    color: "var(--chart-1)",
  },
  TDS: {
    label: "TDS",
    color: "var(--chart-2)",
  },
} satisfies ChartConfig

interface PhTdsChartProps {
  className?: string
}

export function PhTdsChart({ className }: PhTdsChartProps) {
  const [timeRange, setTimeRange] = React.useState("7d")

  const filteredData = chartData.filter((item) => {
    const date = new Date(item.date)
    const referenceDate = new Date("2024-06-30")
    let daysToSubtract = 90
    if (timeRange === "15d") {
      daysToSubtract = 15
    } else if (timeRange === "7d") {
      daysToSubtract = 7
    }
    const startDate = new Date(referenceDate)
    startDate.setDate(startDate.getDate() - daysToSubtract)
    return date >= startDate
  })

  return (
    <Card className={cn("pt-0 gap-0 flex flex-col", className)}>
      <CardHeader className="flex items-center gap-2 space-y-0 border-b py-3 sm:flex-row">
        <div className="grid flex-1 gap-0.5">
          <CardTitle className="text-lg">pH & TDS Levels</CardTitle>
          <CardDescription className="text-xs">
            Water acidity & nutrient concentration
          </CardDescription>
        </div>
        <Select value={timeRange} onValueChange={setTimeRange}>
          <SelectTrigger
            className="hidden h-8 w-[130px] rounded-lg text-xs sm:ml-auto sm:flex"
            aria-label="Select a value"
          >
            <SelectValue placeholder="Last 3 months" />
          </SelectTrigger>
          <SelectContent className="rounded-xl">
            <SelectItem value="90d" className="rounded-lg text-xs">
              Last 3 months
            </SelectItem>
            <SelectItem value="15d" className="rounded-lg text-xs">
              Last 15 days
            </SelectItem>
            <SelectItem value="7d" className="rounded-lg text-xs">
              Last 7 days
            </SelectItem>
          </SelectContent>
        </Select>
      </CardHeader>
      <CardContent className="px-2 pt-3 sm:px-4 sm:pt-3 flex-1 flex flex-col">
        <ChartContainer
          config={chartConfig}
          className="aspect-auto min-h-[280px] h-full w-full flex-1"
        >
          <AreaChart data={filteredData}>
            <defs>
              <linearGradient id="fillPH" x1="0" y1="0" x2="0" y2="1">
                <stop
                  offset="5%"
                  stopColor="var(--color-pH)"
                  stopOpacity={0.8}
                />
                <stop
                  offset="95%"
                  stopColor="var(--color-pH)"
                  stopOpacity={0.1}
                />
              </linearGradient>
              <linearGradient id="fillTDS" x1="0" y1="0" x2="0" y2="1">
                <stop
                  offset="5%"
                  stopColor="var(--color-TDS)"
                  stopOpacity={0.8}
                />
                <stop
                  offset="95%"
                  stopColor="var(--color-TDS)"
                  stopOpacity={0.1}
                />
              </linearGradient>
            </defs>
            <CartesianGrid vertical={false} />
            <XAxis
              dataKey="date"
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              minTickGap={32}
              tickFormatter={(value) => {
                const date = new Date(value)
                return date.toLocaleDateString("en-US", {
                  month: "short",
                  day: "numeric",
                })
              }}
            />
            <ChartTooltip
              cursor={false}
              content={
                <ChartTooltipContent
                  labelFormatter={(value) => {
                    return new Date(value).toLocaleDateString("en-US", {
                      month: "short",
                      day: "numeric",
                    })
                  }}
                  indicator="dot"
                />
              }
            />
            <Area
              dataKey="TDS"
              type="natural"
              fill="#d2e8ff"
              stroke="#8ec5ff"
              stackId="a"
            />
            <Area
              dataKey="pH"
              type="natural"
              fill="#a7caff"
              stroke="#2b7fff"
              stackId="a"
            />
            <ChartLegend content={<ChartLegendContent />} />
          </AreaChart>
        </ChartContainer>
      </CardContent>
    </Card>
  )
}
