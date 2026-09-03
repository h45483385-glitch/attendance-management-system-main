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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employment_type')->default('Permanent')->after('position');
        });

        Schema::table('finger_devices', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('location');
            $table->string('gate')->nullable()->after('branch');
        });

        Schema::table('cameras', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('location');
            $table->string('gate')->nullable()->after('branch');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employment_type');
        });

        Schema::table('finger_devices', function (Blueprint $table) {
            $table->dropColumn(['branch', 'gate']);
        });

        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn(['branch', 'gate']);
        });
    }
};
