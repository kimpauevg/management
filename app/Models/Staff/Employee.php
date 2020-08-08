<?php

namespace App\Models\Staff;

use App\Models\Staff\Salary\SalaryCalcMethod;
use App\Models\Staff\Salary\SalaryCalcRecognizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Class Employee represents employee of the company
 * @package App\Models\Staff
 * @property int $id                    Id of employee
 * @property string $name               Name of employee
 * @property int $salary_type           Id of method used to calculate salary
 * @property float|string $salary       Salary per period
 * @property string $phone              Phone of user
 * @property int $belongs_to_manager    Id of this employee's manager
 * @property string $last_got_salary    When this employee got paid last time
 *
 * @property string $created_at
 * @property string $updated_at
 *
 * @property  Manager|null $asManager   Manager model if this employee is an manager
 * @property  Manager|null $manager     Manager model this user belongs to
 */
class Employee extends Model implements StaffMember
{
    public $fillable = [
        'name', 'salary', 'phone', 'belongs_to_manager', 'salary_type'
    ];

    public $table = 'employee';

    /**
     * Array of rules for validation to create new employee
     *
     * @return array
     */
    public static function createRules()
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'nickname' => [
                'required',
                'string',
                'unique:' . self::class,
            ],
            'salary' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999.99'
            ],
            'salary_type' => [
                'required',
                'integer',
                Rule::in(array_keys(SalaryCalcRecognizer::ALL_TYPES))
            ],
            'phone' => [
                'nullable',
                'string',
                'size:11',
                'unique:' . self::class
            ],
            'belongs_to_manager' => [
                'integer',
                'min:1'
            ]
        ];
    }

    /**
     * Array of rules for validation to update employee
     *
     * @return array
     */
    public function updateRules()
    {
        $rules = self::createRules();
        $unique = 'unique:' . self::class . ',phone';
        if ($this->phone) {
            $unique .= ',' . $this->id;
        }
        $manager_ids = $this->getManagerIds();
        $rules['phone'] = [
            'nullable',
            'string',
            'size:11',
            $unique
        ];
        $rules['belongs_to_manager'][] = Rule::notIn($manager_ids);
        return $rules;
    }

    public function getPositionName(): string
    {
        if ($as_manager = $this->asManager) {
            return $as_manager->getPositionName();
        }
        return 'Employee';
    }

    public function asManager()
    {
        return $this->hasOne(Manager::class, 'employee_id');
    }

    public function manager()
    {
        return $this->hasOne(Manager::class, 'id', 'belongs_to_manager');
    }


    /**
     * Returns salary
     * @return float|string
     * @throws \Exception
     */
    public function countSalary(): string
    {
        $method = SalaryCalcRecognizer::recognizeFor($this);
        return $method->calculateFor($this);
    }

    /**
     * Returns all existing employees as dependency tree
     * All manager's subordinates will be in the subordinates field
     *
     * @return array
     */
    public static function getDependencyTree(): array
    {
        $all_staff = Employee::with('asManager')
            ->get()
            ->map(function (Employee $one) {
                $employee_as_array = $one->attributesToArray();
                $employee_as_array['salary_earned'] = $one->countSalary();
                $employee_as_array['manager_id'] = $one->asManager->id ?? null;
                $employee_as_array['position_name'] = $one->getPositionName();

                $employee_as_array['created_at'] = date('d.m.Y H:i:s', strtotime($one->created_at));
                return $employee_as_array;
            })
            ->toArray();
        $independent_staff = array_filter($all_staff, function ($one) {
            return is_null($one['belongs_to_manager']);
        });
        $staff_graph = [];
        foreach ($independent_staff as $staff) {
            $staff['subordinates'] = [];
            if ($staff['manager_id']) {
                $staff['subordinates'] = self::getDependentEmployees($staff['manager_id'], $all_staff);
            }
            $staff_graph[] = $staff;
        }
        return $staff_graph;
    }

    /**
     * @param string|int $for Manager's id
     * @param array $all_staff All staff that needs to be checked
     * @return array
     */
    private static function getDependentEmployees($for, array $all_staff): array
    {
        $dependent_staff = array_filter($all_staff, function ($one) use ($for) {
            return $one['belongs_to_manager'] == $for;
        });


        $staff_graph = [];
        foreach ($dependent_staff as $staff) {
            $staff['subordinates'] = [];
            if ($staff['manager_id']) {
                $staff['subordinates'] = self::getDependentEmployees($staff['manager_id'], $all_staff);
            }
            $staff_graph[] = $staff;
        }
        return $staff_graph;

    }

    /**
     * Returns all managers' employee ids of this employee
     *
     * @return array
     */
    public function getManagerIds(): array
    {
        $all_ids = [];

        $max_depth = 20;
        $current_depth = 0;
        $employee = $this;
        $manager_above = $employee->manager;

        while (
            $manager_above
            &&
            $current_depth++ < $max_depth
        ) {
            $all_ids[] = $manager_above->asEmployee->id;
            $manager_above = $manager_above->asEmployee->manager;
        }
        return $all_ids;
    }

    /**
     * "Pays" salary to this employee
     */
    public function getPayment()
    {
        $this->last_got_salary = date('Y-m-d H:i:s');
        $this->save();
    }

    public function delete()
    {
        $as_manager = $this->asManager;
        if ($as_manager) {
            $as_manager->delete();
        }
        return parent::delete(); // TODO: Change the autogenerated stub
    }
}