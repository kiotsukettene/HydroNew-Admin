import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

export interface WeeklyFiltrationItem {
  week: string
  filtered: number  // Sum of water_liter from treatment_reports (liters)
  cycles: number
}

interface FiltrationCyclesCardProps {
  weeklyFiltration: WeeklyFiltrationItem[]
}

export function FiltrationCyclesCard({ weeklyFiltration }: FiltrationCyclesCardProps) {
  // Compute stats from water_liter data (filtered) and cycles
  const totalCycles = weeklyFiltration.reduce((sum, item) => sum + item.cycles, 0)
  const totalFiltered = weeklyFiltration.reduce((sum, item) => sum + item.filtered, 0)

  return (
    <Card className="rounded-2xl">
      <CardHeader className="pb-2">
        <CardTitle className="text-base">Filtration Cycles</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4 mt-4">
        <div className="flex items-center justify-between">
          <span className="text-sm text-muted-foreground">In Selected Period</span>
          <span className="text-2xl font-bold text-foreground">{totalCycles}</span>
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center gap-1">
            <span className="text-sm text-muted-foreground">Total Filtered</span>
          </div>
          <span className="text-lg font-semibold text-foreground">{totalFiltered} L</span>
        </div>
      </CardContent>
    </Card>
  )
}
