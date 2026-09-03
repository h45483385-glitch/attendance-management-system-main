<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAndSecurityTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('action');
            $table->string('module');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('before_data')->nullable(); // stored as JSON
            $table->text('after_data')->nullable();  // stored as JSON
            $table->timestamps();
        });

        // 2. Create cameras table
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->string('camera_name');
            $table->string('camera_id')->unique();
            $table->string('device_id')->nullable();
            $table->string('location');
            $table->string('ip');
            $table->string('camera_type')->default('IP Camera');
            $table->string('status')->default('Connected');
            $table->string('assigned_department')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });

        // 3. Create security_settings table
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        // 4. Add columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('Active');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('failed_logins')->default(0);
            $table->timestamp('locked_until')->nullable();
        });

        // 5. Add columns to finger_devices table
        Schema::table('finger_devices', function (Blueprint $table) {
            $table->string('device_id')->unique()->nullable();
            $table->string('type')->default('Fingerprint');
            $table->string('location')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->string('token')->nullable();
            $table->unsignedInteger('registered_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finger_devices', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'type', 'location', 'last_seen', 'token', 'registered_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_login_at', 'last_login_ip', 'failed_logins', 'locked_until']);
        });

        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('cameras');
        Schema::dropIfExists('audit_logs');
    }
}
