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
        Schema::create('hydroponic_yields', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('hydroponic_setup_id')->index('hydroponic_yields_ibfk_1');
            $table->decimal('total_weight', 10)->nullable();
            $table->integer('total_count')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydroponic_yields');
    }
};
