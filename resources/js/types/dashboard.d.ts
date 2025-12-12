export interface DashboardStats {
    totalUsers: number;
    totalDevices: number;
    totalHarvestedCrops: number;
}

export interface HarvestStatus {
    waterTankLevel: number;
    currentGrowthStage: string;
    estimatedHarvestDate: string | null;
    daysRemaining: number | null;
}

export interface DashboardData {
    stats: DashboardStats;
    harvestStatus: HarvestStatus;
}
