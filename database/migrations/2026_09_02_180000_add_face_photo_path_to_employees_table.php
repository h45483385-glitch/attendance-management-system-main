<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacePhotoPathToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     * Adds face_photo_path to store the S3 object key for the enrolled face image.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Stores the S3 object key (path) for the employee's enrolled face photo.
            // e.g. "faces/face_emp_111_1725290000.jpg"
            $table->string('face_photo_path')->nullable()->after('face_enrolled');
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
            $table->dropColumn('face_photo_path');
        });
    }
}
