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
        Schema::create('feedback', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('user_id')->index();
            $table->bigInteger('device_id')->index();
            $table->enum('category', ['bug_report', 'feature_request', 'general_feedback', 'device_issue', 'other'])->default('general_feedback');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
