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
        Schema::create('hydroponic_yield_grades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('hydroponic_yield_id')->index('hydroponic_yield_grades_ibfk_1');
            $table->enum('grade', ['selling', 'consumption', 'disposal']);
            $table->integer('count')->default(0);
            $table->decimal('weight', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydroponic_yield_grades');
    }
};
