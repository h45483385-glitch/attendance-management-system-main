<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\DailyPayLog;

class PayCalculationService
{
    /**
     * ஒரு நாளுக்கான சம்பளத்தை துல்லியமாக கணக்கிடுதல்
     */
    public function calculateDailyPay($employeeId, $checkInTime, $checkOutTime, $dailyWageRate = 140.00, $isPreApprovedOT = false)
    {
        $in = Carbon::parse($checkInTime);
        $out = Carbon::parse($checkOutTime);
        $date = $in->toDateString();

        // 1. மொத்த வேலை செய்த நிமிடங்கள்
        $workedMinutes = $in->diffInMinutes($out);
        $scheduledMinutes = 480; // 8 மணி நேரம்

        // 2. ஒரு நிமிடத்திற்கான சம்பள மதிப்பு (Exact Precision)
        $perMinuteRate = $dailyWageRate / $scheduledMinutes;

        $regularPay = 0.00;
        $shortfallDeduction = 0.00;
        $overtimeMinutes = 0;
        $overtimePay = 0.00;
        $otStatus = 'none';

        if ($workedMinutes < $scheduledMinutes) {
            // நேரம் குறைவு (Shortfall) - வேலை செய்த நிமிடங்களுக்கு மட்டுமே சம்பளம்
            $regularPay = round($workedMinutes * $perMinuteRate, 2);
            $shortfallDeduction = round(($scheduledMinutes - $workedMinutes) * $perMinuteRate, 2);
            $finalPay = $regularPay;
        } else {
            // 8 மணி நேரம் முழுமையாக வேலை செய்தார்
            $regularPay = $dailyWageRate;
            $overtimeMinutes = $workedMinutes - $scheduledMinutes;

            if ($overtimeMinutes > 0) {
                $overtimePay = round($overtimeMinutes * $perMinuteRate, 2);

                if ($isPreApprovedOT) {
                    $otStatus = 'pre_approved';
                    $finalPay = $regularPay + $overtimePay; // உடனே சேர்க்கப்படும்
                } else {
                    $otStatus = 'pending_approval';
                    $finalPay = $regularPay; // அட்மின் அப்ரூவல் தரும் வரை OT சேர்க்கப்படாது
                }
            } else {
                $finalPay = $regularPay;
            }
        }

        // 3. டேட்டாபேஸில் சேமித்தல் / அப்டேட் செய்தல்
        return DailyPayLog::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date],
            [
                'scheduled_minutes' => $scheduledMinutes,
                'worked_minutes' => $workedMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'daily_wage_rate' => $dailyWageRate,
                'regular_pay' => $regularPay,
                'shortfall_deduction' => $shortfallDeduction,
                'overtime_pay' => $overtimePay,
                'overtime_status' => $otStatus,
                'final_day_pay' => $finalPay
            ]
        );
    }
}