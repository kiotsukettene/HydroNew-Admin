<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hydroponic_setup', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('user_id');
            $table->bigInteger('device_id')->nullable()->index('hydroponic_setup_device_id_foreign');
            $table->string('crop_name', 100);
            $table->integer('number_of_crops')->default(0);
            $table->enum('bed_size', ['small', 'medium', 'large', 'custom']);
            $table->json('pump_config')->nullable();
            $table->string('nutrient_solution')->nullable();
            $table->decimal('target_ph_min', 4);
            $table->decimal('target_ph_max', 4);
            $table->decimal('target_tds_min', 6);
            $table->decimal('target_tds_max', 6);
            $table->enum('harvest_status', ['not_harvested', 'harvested'])->nullable()->default('not_harvested');
            $table->enum('growth_stage', ['seedling', 'vegetative', 'flowering', 'harvest-ready', 'harvested', 'overgrown'])->nullable()->default('seedling')->index('idx_hydroponic_setup_growth_stage');
            $table->enum('health_status', ['good', 'moderate', 'poor'])->nullable()->default('good')->index('idx_hydroponic_setup_health_status');
            $table->date('harvest_date')->nullable();
            $table->string('water_amount', 50)->nullable();
            $table->dateTime('setup_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->nullable()->default('active');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['crop_name', 'harvest_date'], 'idx_hydroponic_setup_crop_harvest');
            $table->index(['user_id', 'harvest_status', 'is_archived'], 'idx_hydroponic_setup_user_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydroponic_setup');
    }
};
