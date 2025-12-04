<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            DevicesTableSeeder::class,
            SensorsTableSeeder::class,
            SensorReadingsTableSeeder::class,
            HydroponicSetupTableSeeder::class,
            HydroponicYieldsTableSeeder::class,
            HelpCentersTableSeeder::class,
            TipsSuggestionsTableSeeder::class,
            TreatmentReportsTableSeeder::class,
            TreatmentStagesTableSeeder::class,
            NotificationsTableSeeder::class,
            LoginHistoriesTableSeeder::class,
        ]);
    }
}
