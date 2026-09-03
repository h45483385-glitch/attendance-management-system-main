<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBiometricFlagsAndIndexesToEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('face_enrolled')->default(false)->after('pin_code');
            $table->boolean('fingerprint_enrolled')->default(false)->after('face_enrolled');
            $table->string('status')->default('Active')->after('fingerprint_enrolled');
            
            $table->index('department');
            $table->index('position');
            $table->index('role');
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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropIndex(['position']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            
            $table->dropColumn(['face_enrolled', 'fingerprint_enrolled', 'status']);
        });
    }
}
