<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfessionalFieldsToVisitorsTable extends Migration
{
  
    public function up()
{
    Schema::table('visitors', function (Blueprint $table) {
        // We add these new columns to store the professional details
        $table->string('phone_number')->nullable()->after('name');
        $table->string('purpose_of_visit')->nullable()->after('company');
        $table->string('id_proof_type')->nullable()->after('person_to_meet');
        $table->string('id_proof_number')->nullable()->after('id_proof_type');
    });
}

public function down()
{
    Schema::table('visitors', function (Blueprint $table) {
        // This allows us to undo the changes if needed
        $table->dropColumn(['phone_number', 'purpose_of_visit', 'id_proof_type', 'id_proof_number']);
    });
}
}
