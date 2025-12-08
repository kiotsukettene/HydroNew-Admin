import { useState } from 'react'
import { Droplets } from 'lucide-react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

// Filtration data
const weeklyFiltrationStats = { cycles: 9, totalFiltered: 132 }
const monthlyFiltrationStats = { cycles: 37, totalFiltered: 530 }

export function FiltrationCyclesCard() {
  const [period, setPeriod] = useState<'weekly' | 'monthly'>('monthly')
  const stats = period === 'weekly' ? weeklyFiltrationStats : monthlyFiltrationStats

  return (
    <Card className="rounded-2xl">
      <CardHeader className="pb-2">
        <div className="flex items-center justify-between">
          <CardTitle className="text-base">Filtration Cycles</CardTitle>
          <div className="flex gap-1">
            <Button
              variant={period === 'weekly' ? 'primary' : 'secondary'}
              size="sm"
              onClick={() => setPeriod('weekly')}
              className="w-auto"
            >
              Weekly
            </Button>
            <Button
              variant={period === 'monthly' ? 'primary' : 'secondary'}
              size="sm"
              onClick={() => setPeriod('monthly')}
              className="w-auto"
            >
              Monthly
            </Button>
          </div>
        </div>
      </CardHeader>
      <CardContent className="space-y-4 mt-4">
        <div className="flex items-center justify-between">
          <span className="text-sm text-muted-foreground">
            {period === 'weekly' ? 'This Week' : 'This Month'}
          </span>
          <span className="text-2xl font-bold text-foreground">{stats.cycles}</span>
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center gap-1">
            <span className="text-sm text-muted-foreground">Total Filtered</span>
          </div>
          <span className="text-lg font-semibold text-foreground">{stats.totalFiltered} L</span>
        </div>
      </CardContent>
    </Card>
  )
}

