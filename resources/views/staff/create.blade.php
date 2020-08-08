<?php
use App\Models\Staff\Salary\SalaryCalcRecognizer;
use App\Models\Staff\Employee;
$exists = false;
$employee_ids = [];
$is_manager = 0;
if (
    isset($employee)
    &&
    $employee instanceof \App\Models\Staff\Employee
) {
    $exists = true;
    $id = $employee->id;
    $name = $employee->name;
    $phone = $employee->phone;
    $salary_type = $employee->salary_type;
    $salary = $employee->salary;
    $as_manager = $employee->asManager;
    $is_manager = (int)(bool) $as_manager;
    if ($is_manager) {
        $employee_ids = $as_manager
            ->subordinates
            ->map(function (Employee $subordinate) {
                return $subordinate->id;
            })
            ->toArray();
    }
}
$employee_ids = old('subordinate', $employee_ids);
$is_manager = old('is_manager', $is_manager);
?>
@extends('layouts.app')
@section('styles')
    <style>
        .manager-button{
            display: none;
        }
        .add-button {
            margin: 15px;
        }
        #example {
            display: none;
        }
        .input-with-text > input {
            width: auto;
            display: inline;
        }
        #subordinates .invalid-feedback {
            display: block;
        }
        .rm-button {
            max-width: 30px;
            max-height: 30px;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ $header ?? 'Create new employee' }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ $post_route ?? route('staff.store') }}">
                            {{ csrf_field() }}
                            @if($exists)
                                <div class="form-group row">
                                    <label for="name" class="col-md-4 col-form-label text-md-right">ID</label>

                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" value="{{$id}}" disabled>
                                    </div>
                                </div>

                            @endif

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $name ?? null) }}" required autocomplete="name" autofocus>

                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="phone" class="col-md-4 col-form-label text-md-right">Phone</label>

                                <div class="col-md-6 input-with-text">
                                    <span>
                                        +
                                    </span>
                                    <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $phone ?? null) }}"  autocomplete="phone" autofocus>

                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <!--Salary period-->
                            <div class="form-group row">
                                <label for="salary_type" class="col-md-4 col-form-label text-md-right">Salary period</label>

                                <div class="col-md-6">
                                    <select name="salary_type">
                                        <option disabled>Choose one</option>
                                        @foreach(SalaryCalcRecognizer::getAll() as $key => $type)
                                            <option {{$key == old('salary_type', $salary_type ?? null) ? 'selected':''}} value="{{$key}}">{{$type::methodName()}}</option>
                                        @endforeach
                                    </select>
                                    @error('salary_type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="salary" class="col-md-4 col-form-label text-md-right">Salary</label>

                                <div class="col-md-6">
                                    <div class="input-with-text">
                                        <input id="salary" type="number" min="0" step="0.01" class="form-control @error('salary') is-invalid @enderror" name="salary" value="{{ old('salary', $salary ?? null) }}" required autocomplete="salary" autofocus>
                                        <span class="input-text">
                                            $/period
                                        </span>
                                        @error('salary')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <div id="subordinates" class="form-group row" style="display: none">
                                <label class="col-md-4 col-form-label text-md-right">Subordinates</label>
                                <div class="col-md-8">
                                    <div class="subordinates-body">
                                    </div>


                                    <div id="example" class="form-group row">
                                        <label for="subordinate" class="col-md-4 col-form-label text-md-right">Subordinate ID</label>
                                        <div class="col-md-6">
                                            <input id="subordinate" type="number" min="1" class="form-control" name="subordinate[]" disabled>
                                        </div>
                                        <button type="button" class="rm-button" onclick="$(this).parent().remove()">X</button>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <button type="button" class="add-button">
                                                Add subordinate field
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Save
                                    </button>
                                    <button id="turn-to-manager" type="button" class="btn btn-primary manager-button">
                                        Turn into a manager
                                    </button>
                                    <button id="remove-from-managers" type="button" class="btn btn-primary manager-button">
                                        Remove from managers
                                    </button>
                                    <input type="hidden" name="is_manager" value="{{$is_manager}}">
                                    @if($exists)
                                        <button id="delete" type="button" class="btn btn-danger">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script defer>
        $(document).ready(() => {
            let invis = '';
            @if($is_manager)
                invis = '{{'remove-from-managers'}}';
                $("#subordinates").css('display', '');

            @else
                invis = '{{'turn-to-manager'}}';

            @endif
            $('#' + invis).css('display', 'initial');
            @if($exists)
            $('#delete').click(() => {
                if (confirm('Are you sure you want to delete this employee?')) {
                    $.ajax({
                        url: '{{route('staff.destroy', [
                        'staff' => $id
                    ])}}',
                        success: function () {
                            window.location.replace('{{route('staff.index')}}')
                        }
                    })
                }
            });
            @endif
            $("#turn-to-manager").click(function() {
                let input = $('input[name=is_manager]');
                input.val(1);
                $(this).css('display', 'none')
                $("#remove-from-managers").css('display', 'initial');
                $("#subordinates").css('display', '')

            })
            $("#remove-from-managers").click(function () {
                let input = $('input[name=is_manager]');
                input.val(0);
                $(this).css('display', 'none');
                $("#turn-to-manager").css('display', 'initial');
                $("#subordinates").css('display', 'none')

            })
            $('.add-button').click(() => {
                addSubordinateField()
            });
            function addSubordinateField(value = false, errors = '') {
                let example_field = $('#example').clone();
                example_field.removeAttr('id', '');
                let input = example_field.find('input');
                input.removeAttr('disabled');
                if (value) {
                    input.val(value);
                }
                input.after(errors);
                $('.subordinates-body').append(example_field);
            }
            let error_span = '';
            @foreach($employee_ids as $key => $id)
                    error_span ='';
                    @error('subordinate.' . $key)
                        error_span =
                        '<span class="invalid-feedback" role="alert">' +
                            '<strong>{{ $message }}</strong>' +
                        '</span>';
                    @enderror
                addSubordinateField({{$id}}, error_span);
            @endforeach


        });
    </script>
@endsection