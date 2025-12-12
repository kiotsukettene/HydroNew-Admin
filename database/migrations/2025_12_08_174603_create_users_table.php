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
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique('email');
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('address')->nullable();
            $table->boolean('first_time_login')->nullable()->default(true);
            $table->dateTime('last_login_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->timestamp('last_otp_sent_at')->nullable();
            $table->enum('roles', ['admin', 'user'])->default('user');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
