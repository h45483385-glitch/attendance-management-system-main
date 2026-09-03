<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camera_health_logs', function (Blueprint $table) {
            $table->id();
            $table->string('camera_id');
            $table->enum('status', ['ONLINE', 'DEGRADED', 'OFFLINE', 'UNKNOWN'])->default('UNKNOWN');
            $table->integer('latency_ms')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->integer('consecutive_successes')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            // Foreign key relation (assuming camera_id links to cameras.camera_id)
            // But if it's string, just indexing is fine
            $table->index('camera_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camera_health_logs');
    }
};
