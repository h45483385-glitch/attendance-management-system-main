<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id'); 
            $table->string('month_year');
            $table->integer('total_working_days');
            $table->integer('total_present');
            $table->integer('total_absent');
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_reports');
    }
}