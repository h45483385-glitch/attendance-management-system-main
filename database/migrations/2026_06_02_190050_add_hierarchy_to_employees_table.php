<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHierarchyToEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Defines who the user is (HR, TL, or standard Employee)
            $table->string('role')->default('Employee')->after('email');
            
            // Creates the relational link to the Team Lead
            // ... the rest of your code ...
        }); // <-- Make sure this closure is closed
    } // <-- Make sure the up() function is closed

    public function down()
    {
        // ... your down logic ...
    }
} // <-- Make sure the class is closed