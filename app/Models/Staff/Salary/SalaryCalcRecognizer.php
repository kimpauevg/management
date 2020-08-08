<?php


namespace App\Models\Staff\Salary;

use App\Models\Staff\Employee;
use App\Models\Staff\Salary\SalaryCalcMethod;

class SalaryCalcRecognizer
{
    const ALL_TYPES = [
        1 => HourlyMethod::class,
        2 => MonthlyMethod::class
    ];

    /**
     * Recognizes salary calculation method for given employee
     * @param Employee $employee
     * @return \App\Models\Staff\Salary\SalaryCalcMethod
     * @throws \Exception
     */
    public static function recognizeFor(Employee $employee): SalaryCalcMethod
    {
        $id = $employee->salary_type;
        return self::recognizeById($id);
    }

    /**
     * Get salary calculation method for
     * @param int $id
     * @return \App\Models\Staff\Salary\SalaryCalcMethod
     * @throws \Exception
     */
    public static function recognizeById(int $id): SalaryCalcMethod
    {
        if (in_array($id, array_keys(self::ALL_TYPES))) {
            $class_name = self::ALL_TYPES[$id];
            return new $class_name;
        }
        throw new \Exception('Unrecognizable calculation method '. $id);
    }

    /**
     * Get all registered salary calculation methods
     * @return SalaryCalcMethod[]
     */
    public static function getAll(): array
    {
        $result = [];
        foreach (self::ALL_TYPES as $key => $type) {
            $class_name = self::ALL_TYPES[$key];
            $result[$key] = new $class_name;
        }
        return $result;
    }

}