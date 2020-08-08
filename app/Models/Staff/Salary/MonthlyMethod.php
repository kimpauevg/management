<?php


namespace App\Models\Staff\Salary;


use App\Models\Staff\Employee;

class MonthlyMethod implements SalaryCalcMethod
{
    private static $salary_per_subordinate = 5000;

    public static function methodName(): string
    {
        return 'Monthly';
    }

    public static function calculateFor(Employee $employee): string
    {
        $seconds_in_month = 30.419 * 24 * 60 * 60;
        $salary = $employee->salary;
        if ($as_manager = $employee->asManager) {
            $subordinates_amount = $as_manager->subordinates->count();
            $salary += self::$salary_per_subordinate * $subordinates_amount;
        }
        $sum = $salary * (time() - strtotime($employee->last_got_salary)) / $seconds_in_month;

        return number_format($sum, 2);
    }
}