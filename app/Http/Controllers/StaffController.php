<?php


namespace App\Http\Controllers;


use App\Http\Controllers\ActionModels\Staff\Index;
use App\Http\Controllers\ActionModels\Staff\Store;
use App\Http\Controllers\ActionModels\Staff\Update;
use App\Models\API\Staff\Pay;
use App\Models\Staff\Employee;
use App\Models\Staff\Manager;
use http\Env\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    /**
     * Main page
     *
     * @param Index $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Index $request)
    {
        $staff = $request->getEmployeeDependencyTree();
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
     * @param Store $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Store $request)
    {
        $request_data = $request->validated();
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
     * @param Update $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function update($id, Update $request)
    {
        $request_data = $request->validated();
        $employee = $request->employee;
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
     * @param Pay $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function pay(Pay $request)
    {
        $staff = $request->validated()['staff_to_pay'] ?? [];
        /** @var Collection $employees */
        $employees = Employee::whereIn('id', $staff)->get();
        $employees->each(function (Employee $employee) {
            $employee->getPayment();
        });
        return response()->json([
            'message' => 'success'
        ]);
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