<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnterpriseColumnsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('company_name')->default('Enterprise AMS Corp')->after('id');
            $table->string('timezone')->default('Asia/Kolkata')->after('company_name');
            $table->string('financial_year_start')->default('04-01')->after('timezone');
            $table->string('currency_symbol')->default('₹')->after('financial_year_start');
            $table->string('currency_code')->default('INR')->after('currency_symbol');

            // Notification triggers
            $table->boolean('notify_payroll_email')->default(true)->after('min_full_day_hours');
            $table->boolean('notify_payroll_sms')->default(false)->after('notify_payroll_email');
            $table->boolean('notify_late_alerts')->default(true)->after('notify_payroll_sms');
            $table->string('alert_email')->nullable()->after('notify_late_alerts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'timezone',
                'financial_year_start',
                'currency_symbol',
                'currency_code',
                'notify_payroll_email',
                'notify_payroll_sms',
                'notify_late_alerts',
                'alert_email'
            ]);
        });
    }
}
