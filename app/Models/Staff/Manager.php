<?php

namespace App\Models\Staff;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class Manager
 * @package App\Models\Staff
 * @property int $id                    Id of manager
 * @property int $employee_id           Manager's employee id
 *
 * @property Collection $subordinates   Array of employees that belong to that manager
 * @property Employee $asEmployee       Employee which that manager is
 */
class Manager extends Model implements StaffMember
{
    public $table = 'manager';

    public $fillable = ['employee_id'];

    public function getPositionName(): string
    {
        return 'Manager';
    }

    public static function createRules()
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                'unique:' . self::class,
                'exists:' . Employee::class . ',id'
            ],
        ];
    }

    public function subordinateRules()
    {
        $each_subordinate = [
            'required_if:is_manager,1',
            'integer',
            'exists:' . Employee::class . ',id',
        ];
        if ($this->asEmployee->belongs_to_manager ?? false) {
            $ids = $this->asEmployee->getManagerIds();
            $each_subordinate[] =  Rule::notIn($ids);
        }
        return [
            'is_manager' => [
                'boolean'
            ],
            'subordinate' => [
                'array'
            ],
            'subordinate.*' => $each_subordinate
        ];
    }

    public static function subordinateMessages()
    {
        return [
            'not_in' => 'This id belongs to that employee or to the manager above him',
            'subordinate.*.exists' => 'Employee with that id does not exist'
        ];
    }

    public static function subordinateAttributes()
    {
        return [
            'subordinate.*' => 'subordinate'
        ];
    }


    public function manager()
    {
        return $this->asEmployee->manager;
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'belongs_to_manager');
    }

    public function asEmployee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function countSalary(): string
    {
        return $this->asEmployee->countSalary();
    }

    public function setSubordinates($array_of_ids)
    {

        $table = (new Employee())->table;
        DB::table($table)
            ->where('belongs_to_manager', $this->id)
            ->update(['belongs_to_manager' => null]);
        DB::table($table)
            ->whereIn('id', $array_of_ids)
            ->update(['belongs_to_manager' => $this->id]);
    }

    public function rmSubordinates()
    {
        $table = (new Employee())->table;
        $db = DB::table($table);
        $db->update(
            ['belongs_to_manager' => null],
            ['belongs_to_manager' => $this->id]
        );
    }

}