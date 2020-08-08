<?php


namespace App\Models\Staff\Salary;


use App\Models\Staff\Employee;

/**
 * Method used to calculate salary based on
 *
 * @package App\Models\Staff\Salary
 */
class HourlyMethod implements SalaryCalcMethod
{
    private static $subordinate_coefficient = 1.05;

    public static function methodName(): string
    {
        return 'Hourly';
    }

    public static function calculateFor(Employee $employee): string
    {
        $seconds_in_hour = 3600;
        $sum = $employee->salary * (time() - strtotime($employee->last_got_salary)) / $seconds_in_hour;
        if ($as_manager = $employee->asManager) {
            $subordinates_amount = $as_manager->subordinates->count();
            $sum *= pow(self::$subordinate_coefficient, $subordinates_amount);
        }
        return number_format($sum, 2);
    }
}