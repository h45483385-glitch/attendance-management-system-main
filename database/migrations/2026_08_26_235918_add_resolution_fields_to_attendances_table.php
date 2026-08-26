<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // after() கன்டிஷனை நீக்கிவிட்டோம்
            $table->string('resolution_status')->nullable();
            $table->decimal('penalty_amount', 8, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['resolution_status', 'penalty_amount']);
        });
    }
};