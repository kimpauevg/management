<?php

namespace App\Models\Staff\Salary;

use App\Models\Staff\Employee;

interface SalaryCalcMethod
{
    /**
     * Returns how much employee has earned
     * @param Employee $employee
     * @return string|float
     */
    public static function calculateFor(Employee $employee): string;

    /**
     * Returns the name of this method
     * @return string
     */
    public static function methodName(): string;
}