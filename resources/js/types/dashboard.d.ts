export interface DashboardStats {
    totalUsers: number;
    totalDevices: number;
    totalHarvestedCrops: number;
}

export interface WaterTreatmentStats {
    totalCycles: number;
    successRate: number;
    averageDuration: number;
    successfulCycles: number;
    failedCycles: number;
}

export interface DashboardData {
    stats: DashboardStats;
    waterTreatmentStats: WaterTreatmentStats;
}
