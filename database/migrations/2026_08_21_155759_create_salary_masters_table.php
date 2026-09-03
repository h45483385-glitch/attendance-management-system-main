<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('salary_masters');

        Schema::create('salary_masters', function (Blueprint $table) {
            $table->id();
            
            // Department and Designation
            $table->string('department'); 
            $table->string('designation'); 
            
            // Base Salary
            $table->decimal('current_base_salary', 10, 2); 
            
            // Timer/Scheduled Salary
            $table->decimal('scheduled_salary', 10, 2)->nullable(); 
            $table->date('effective_date')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_masters');
    }
};