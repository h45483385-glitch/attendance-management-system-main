<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // This line drops the broken table if it already exists before creating the new one
        Schema::dropIfExists('daily_pay_logs');

        Schema::create('daily_pay_logs', function (Blueprint $table) {
            $table->id();
            
            // Using unsignedInteger to match your users table ID format perfectly
            $table->unsignedInteger('employee_id'); 
            
            $table->date('date');
            
            // Time Calculations
            $table->integer('scheduled_minutes')->default(480);
            $table->integer('worked_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            
            // Pay Calculations
            $table->decimal('daily_wage_rate', 10, 2);
            $table->decimal('regular_pay', 10, 2)->default(0.00);
            $table->decimal('shortfall_deduction', 10, 2)->default(0.00);
            $table->decimal('overtime_pay', 10, 2)->default(0.00);
            
            // Approvals & Final Pay
            $table->string('overtime_status')->default('none');
            $table->text('admin_remarks')->nullable();
            $table->decimal('final_day_pay', 10, 2)->default(0.00);
            
            $table->timestamps();

            // Referencing the 'users' table to avoid foreign key constraint errors
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_pay_logs');
    }
};