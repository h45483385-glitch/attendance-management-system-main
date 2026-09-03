<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index('attendance_date');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index('status');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('break_logs', function (Blueprint $table) {
                $table->index('attendance_id');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('break_logs', function (Blueprint $table) {
                $table->index('break_start');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('action');
            });
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('daily_pay_logs')) {
                Schema::table('daily_pay_logs', function (Blueprint $table) {
                    $table->index('date');
                });
            }
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('overtimes')) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->index('overtime_date');
                });
            }
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('leaves')) {
                Schema::table('leaves', function (Blueprint $table) {
                    $table->index('leave_date');
                });
            }
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('latetimes')) {
                Schema::table('latetimes', function (Blueprint $table) {
                    $table->index('latetime_date');
                });
            }
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropIndex(['attendance_date']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('break_logs', function (Blueprint $table) {
                $table->dropIndex(['attendance_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('break_logs', function (Blueprint $table) {
                $table->dropIndex(['break_start']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['action']);
            });
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('daily_pay_logs')) {
                Schema::table('daily_pay_logs', function (Blueprint $table) {
                    $table->dropIndex(['date']);
                });
            }
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('overtimes')) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->dropIndex(['overtime_date']);
                });
            }
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('leaves')) {
                Schema::table('leaves', function (Blueprint $table) {
                    $table->dropIndex(['leave_date']);
                });
            }
        } catch (\Exception $e) {}

        try {
            if (Schema::hasTable('latetimes')) {
                Schema::table('latetimes', function (Blueprint $table) {
                    $table->dropIndex(['latetime_date']);
                });
            }
        } catch (\Exception $e) {}
    }
}
