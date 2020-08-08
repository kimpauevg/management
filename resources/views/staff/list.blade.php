@extends('layouts.app')
@section('styles')
    <style>
        .control-row {
            background: grey;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 30px;
            width: auto;
            margin-left: auto;
            margin-right: auto;
        }
        .staff-table, .staff-table-row, .staff-table-cell {
            border: 1px solid black;
        }
        .staff-table {
            width: auto;
        }
        .staff-table-row {
            display: flex;
            flex-direction: row;
        }
        .staff-subtable {
            width: 97%;
            margin-left: auto;
        }
        .first {
            width: 100%;
        }
        .staff-table-cell {
            display: flex;
            padding: 5px;
            overflow: hidden;
        }
        .staff-table-cell {
            width: 25%;
        }
        .staff-table-cell:nth-child(1) {
            width: 5%;
        }
        .staff-table-cell:nth-child(2), .staff-table-cell:nth-child(3) {
            width: 20%;
        }
        .staff-table-cell:nth-child(4) {
            width: 10%;
        }
        .staff-table-cell:nth-child(5) {
            width: 10%;
        }
        .staff-table-cell:last-child {
            width: 15%;
        }

        .staff-table > .staff-table-subrow {
            width: 100%;
        }
        form {
            margin-bottom: 0;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row control-row col-md-8">
                <div class="col-md-5 offset-md-1">
                    <a href="{{route('staff.create')}}" class="btn btn-primary">
                        Create new employee
                    </a>
                </div>
            <div class="col-md-5 offset-md-1">
                <button type="submit" form="pay" class="btn btn-primary">
                    Pay salary
                </button>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="table staff-table">
                    <form id="pay" method="POST" action="/staff/pay">
                        @csrf
                        {{view('staff.subtable', [
                            'staff' => $staff,
                            'first' => 'first'
                        ])}}

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {

            $('input.checkbox-all').click(function () {
                let check = $(this);
                let search = $(check.parents('.staff-subtable')[0]);
                if (this.checked) {
                    search.find('input').each(function () {
                        this.checked = true;
                    })
                } else {
                    search.find('input').each(function () {
                        this.checked = false;
                    })

                }
            })
        })
    </script>
@endsection