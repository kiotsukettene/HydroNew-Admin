export interface UsersDevicesAnalytics {
    users: {
        total: number;
        active: number;
        inactive: number;
        archived: number;
        without_devices: number;
    };
    devices: {
        total: number;
        online: number;
        offline: number;
    };
    registration_trend: Array<{
        month: string;
        count: number;
    }>;
    login_activity_trend: Array<{
        date: string;
        unique_users: number;
        total_logins: number;
    }>;
}

export interface CropsHarvestYieldAnalytics {
    // Harvest metrics
    setups_by_status: Record<string, number>;
    growth_stage_distribution: Record<string, number>;
    health_status_distribution: Record<string, number>;
    harvest_rate: number;
    harvest_this_month: number;
    harvest_this_year: number;
    
    // Yield metrics
    total_yield_weight: number;
    total_yield_count: number;
    average_yield_per_setup: number;
    
    // Combined crop insights
    popular_crops: Array<{
        crop_name: string;
        count: number;
    }>;
    most_grown_crop: string | null;
    crop_type_distribution: Array<{
        crop: string;
        harvested: number;
    }>;
    top_yielding_crops: Array<{
        crop_name: string;
        total_weight: number;
        setup_count: number;
    }>;
    
    // Grade distribution
    grade_distribution: Record<string, {
        weight: number;
        count: number;
        percentage: number;
    }>;
    
    // Combined trends
    monthly_harvest_trend: Array<{
        month: string;
        harvested: number;
        total_weight: number;
    }>;
}

export interface WaterTreatmentAnalytics {
    total_cycles: number;
    successful_cycles: number;
    failed_cycles: number;
    pending_cycles: number;
    success_rate: number;
    failure_rate: number;
    average_duration: number;
    stage_performance: Array<{
        stage_name: string;
        total_count: number;
        passed_count: number;
        failed_count: number;
        pass_rate: number;
        avg_ph: number | null;
        avg_turbidity: number | null;
        avg_tds: number | null;
    }>;
    treatment_trends: Array<{
        date: string;
        cycle_count: number;
        success_count: number;
    }>;
    weekly_filtration: Array<{
        week: string;
        filtered: number;
        cycles: number;
    }>;
}

