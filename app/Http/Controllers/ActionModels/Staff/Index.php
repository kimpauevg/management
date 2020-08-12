<?php

namespace App\Http\Controllers\ActionModels\Staff;

use App\Models\Staff\Employee;
use Illuminate\Foundation\Http\FormRequest;

class Index extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }
    /**
     * Returns all existing employees as dependency tree
     * All manager's subordinates will be in the subordinates field
     *
     * @return array
     */
    public function getEmployeeDependencyTree(): array
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
        $keys = array_keys($independent_staff);
        foreach ($keys as $key_to_unset) {
            unset($all_staff[$key_to_unset]);
        }
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

        $keys = array_keys($dependent_staff);
        foreach ($keys as $key_to_unset) {
            unset($all_staff[$key_to_unset]);
        }

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

}