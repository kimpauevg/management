<?php


namespace App\Http\Controllers\ActionModels\Staff;


use App\Models\Staff\Employee;
use App\Models\Staff\Manager;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class Update
 * @package App\Http\Controllers\ActionModels\Staff
 *
 * @property Employee $employee Employee found with passed id
 */
class Update extends FormRequest
{
    public $employee;
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
        $id = $this->route('staff');
        $employee = Employee::find($id);
        if (!$employee) {
            abort(404);
        }
        $this->employee = $employee;
        if ($as_manager = $employee->asManager) {
            $manager_rules = $as_manager->subordinateRules();
        } else {
            $manager_rules = (new Manager())->subordinateRules();
        }
        $manager_rules['subordinate.*'][] = 'not_in:' . $id;
        $rules = array_merge($employee->updateRules(), $manager_rules);
        return $rules;
    }

    public function messages()
    {
        $msgs = array_merge(parent::messages(), Manager::subordinateMessages());
        return $msgs;
    }

    public function attributes()
    {
        $attrs = array_merge(parent::attributes(), Manager::subordinateAttributes());
        return $attrs;
    }


}