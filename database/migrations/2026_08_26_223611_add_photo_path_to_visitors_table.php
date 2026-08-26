<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // ID Number-க்கு அடுத்து புதுசா photo_path காலம் சேர்க்கிறோம்
            $table->string('photo_path')->nullable()->after('id_number');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};