@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("Edit Time Slot")}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-6">
                <div class="panel">
                    <div class="panel-title">{{__("Edit Time Slot for ")}} "{{ $tour->title }}"</div>
                    <div class="panel-body">
                        <form action="{{ route('tour.admin.time_slots.update', ['id' => $row->id]) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label>{{__("Day of Week")}}</label>
                                <select name="day_of_week" class="form-control">
                                    <option value="1" @if($row->day_of_week == 1) selected @endif>{{__("Monday")}}</option>
                                    <option value="2" @if($row->day_of_week == 2) selected @endif>{{__("Tuesday")}}</option>
                                    <option value="3" @if($row->day_of_week == 3) selected @endif>{{__("Wednesday")}}</option>
                                    <option value="4" @if($row->day_of_week == 4) selected @endif>{{__("Thursday")}}</option>
                                    <option value="5" @if($row->day_of_week == 5) selected @endif>{{__("Friday")}}</option>
                                    <option value="6" @if($row->day_of_week == 6) selected @endif>{{__("Saturday")}}</option>
                                    <option value="7" @if($row->day_of_week == 7) selected @endif>{{__("Sunday")}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{__("Start Time")}}</label>
                                <input type="time" name="start_time" class="form-control" value="{{ $row->start_time }}">
                            </div>
                            <div class="form-group">
                                <label>{{__("Max Guests")}}</label>
                                <input type="number" name="max_guests" class="form-control" min="1" value="{{ $row->max_guests }}">
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="active" value="1" @if($row->active) checked @endif> {{__("Active")}}
                                </label>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">{{__("Save Changes")}}</button>
                                <a href="{{ route('tour.admin.time_slots', ['tour_id' => $tour->id]) }}" class="btn btn-default">{{__("Cancel")}}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
