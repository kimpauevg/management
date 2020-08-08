<?php


namespace App\Http\Controllers;


use App\Models\Staff\Employee;
use App\Models\Staff\Manager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    /**
     * Main page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $staff = Employee::getDependencyTree();
        return view('staff.list', [
            'staff' => $staff
        ]);
    }

    /**
     * Employee creation form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('staff.create');
    }

    /**
     * Create new employee
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $rules = array_merge(Employee::createRules(), (new Manager())->subordinateRules());
        $validator = Validator::make(
            $request->all(),
            $rules,
            Manager::subordinateMessages(),
            Manager::subordinateAttributes()
        );
        $request_data = $validator->validated();
        $employee = Employee::create($request_data);
        if ($request_data['is_manager'] ?? false) {
            $as_manager = Manager::create([
                'employee_id' => $employee->id
            ]);
            $as_manager->setSubordinates($request_data['subordinate'] ?? []);
        }

        return redirect('/staff');
    }

    /**
     * Employee editing form
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        /** @var Employee $employee */
        $employee = Employee::find($id);
        if (!$employee) {
            abort(404);
        }
        return view('staff.create', [
            'employee' => $employee,
            'post_route' => '/staff/' . $id,
            'header' => 'Change employee data'
        ]);
    }

    /**
     * Update already existing employee
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function update($id, Request $request)
    {
        /** @var Employee $employee */
        $employee = Employee::find($id);
        if (!$employee) {
            abort(404);
        }
        if ($as_manager = $employee->asManager) {
            $manager_rules = $as_manager->subordinateRules();
        } else {
            $manager_rules = (new Manager())->subordinateRules();
        }
        $manager_rules['subordinate.*'][] = 'not_in:' . $id;
        $rules = array_merge($employee->updateRules(), $manager_rules);
        $validator = Validator::make(
            $request->all(),
            $rules,
            Manager::subordinateMessages(),
            Manager::subordinateAttributes()
        );
        $validator->validate();
        $request_data = $validator->validated();
        $employee->fill($request_data)->save();
        $as_manager = $employee->asManager;
        if ($request_data['is_manager'] ?? false) {
            if (!$as_manager) {
                $as_manager = Manager::create([
                    'employee_id' => $id
                ]);
            }
            $as_manager->setSubordinates($request_data['subordinate'] ?? []);
        } else {
            if ($as_manager) {
                $as_manager->delete();
            }
        }

        return redirect('/staff');
    }

    /**
     * Pay salary to employees
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_to_pay' => [
                'required',
                'array',
            ],
            'staff_to_pay.*' => [
                'integer',
                'exists:employee,id'
            ],
        ]);
        $validator->validate();
        $staff = $validator->validated()['staff_to_pay'];
        /** @var Collection $employees */
        $employees = Employee::whereIn('id', $staff)->get();
        $employees->each(function (Employee $employee) {
            $employee->getPayment();
        });
        return redirect('/staff');
    }

    /**
     * Remove employee
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function destroy($id)
    {
        /** @var Employee $employee */
        $employee = Employee::find($id);
        if (!$employee) {
            abort(404);
        }
        $employee->delete();
        return redirect('/staff');
    }
}