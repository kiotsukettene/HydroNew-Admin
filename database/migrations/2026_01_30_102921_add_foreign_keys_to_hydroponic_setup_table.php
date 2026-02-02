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
        Schema::table('hydroponic_setup', function (Blueprint $table) {
            $table->foreign(['device_id'])->references(['id'])->on('devices')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hydroponic_setup', function (Blueprint $table) {
            $table->dropForeign('hydroponic_setup_device_id_foreign');
        });
    }
};
