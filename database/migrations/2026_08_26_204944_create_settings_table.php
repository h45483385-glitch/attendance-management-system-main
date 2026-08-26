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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->time('shift_start')->default('09:30:00');
            $table->time('shift_end')->default('18:30:00');
            $table->integer('grace_period')->default(10);
            $table->boolean('is_saturday_off')->default(false);
            $table->boolean('is_sunday_off')->default(true);
            $table->integer('casual_leaves')->default(12);
            $table->integer('medical_leaves')->default(6);
            $table->integer('min_full_day_hours')->default(8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};