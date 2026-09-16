<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebauthnCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     * Zero-Storage Biometrics Architecture (FIDO2 / WebAuthn)
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('webauthn_credentials')) {
            Schema::create('webauthn_credentials', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('credential_id', 500)->unique();
                $table->text('public_key'); // Public Key only - ZERO biometric templates
                $table->unsignedInteger('counter')->default(0);
                $table->string('device_name')->default('Windows Hello / Device Biometrics');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webauthn_credentials');
    }
}
