<div class="staff-subtable {{$first ?? ''}}">
        <div class="staff-table-row">
            <div class="d-md-table-cell staff-table-cell">
                <input class="checkbox-all" type="checkbox">
            </div>
            <div class="d-md-table-cell staff-table-cell">
                Name
            </div>
            <div class="d-md-table-cell staff-table-cell">
                Position
            </div>

            <div class="d-md-table-cell staff-table-cell">
                Emp. ID
            </div>
            <div class="d-md-table-cell staff-table-cell">
                Mgr. ID
            </div>
            <div class="d-md-table-cell staff-table-cell">
                Works since
            </div>
            <div class="d-md-table-cell staff-table-cell">
                Salary per period, $
            </div>
            <div class="d-md-table-cell staff-table-cell">
                Salary earned, $
            </div>
        </div>
    @foreach($staff as $member)

        <div class="staff-table-row">
            <div class="staff-table-cell">
                <input class="checkbox-self" type="checkbox" name="staff_to_pay[]" value="{{$member['id']}}">
            </div>
            <div class="staff-table-cell">
                <a href="{{route('staff.edit', ['staff' => $member['id']])}}">
                    {{$member['name']}}
                </a>
            </div>
            <div class="staff-table-cell">
                {{$member['position_name']}}
            </div>
            <div class="staff-table-cell">
                {{$member['id']}}
            </div>
            <div class="staff-table-cell">
                {{$member['manager_id']}}
            </div>

            <div class="staff-table-cell">
                {{$member['created_at']}}
            </div>
            <div class="staff-table-cell">
                {{$member['salary']}}
            </div>
            <div class="staff-table-cell">
                {{$member['salary_earned']}}
            </div>
        </div>
        @if(
        isset($member['subordinates'])
        &&
        $member['subordinates']
        &&
        is_array($member['subordinates'])
        )
            {{view('staff.subtable', [
                'staff' => $member['subordinates']
            ])}}
        @endif
    @endforeach
</div>
