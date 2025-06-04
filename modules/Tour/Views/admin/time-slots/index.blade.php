{{-- File: modules/Tour/Views/admin/time-slots/index.blade.php --}}

@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between">
        <h1 class="h3 mb-0 text-gray-800">{{__('Time Slots for :tour', ['tour' => $tour->title])}}</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTimeSlotModal">
            <i class="fa fa-plus"></i> {{__('Add Time Slot')}}
        </button>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="row">
                        <div class="col-6">
                            <h6 class="m-0 font-weight-bold text-primary">{{__('Time Slots Management')}}</h6>
                        </div>
                        <div class="col-6">
                            <div class="float-right">
                                <form method="GET" class="form-inline">
                                    <select name="day_of_week" class="form-control form-control-sm mr-2">
                                        <option value="">{{__('All Days')}}</option>
                                        @for($i = 1; $i <=7; $i++)
                                            <option value="{{$i}}" @if(request('day_of_week') == $i) selected @endif>
                                                {{['', __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday')][$i]}}
                                            </option>
                                        @endfor
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">{{__('Filter')}}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($rows->count() > 0)
                    <form id="bulkForm" method="POST" action="{{route('tour.admin.time_slots.bulk_action', $tour->id)}}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <select name="action" class="form-control form-control-sm mr-2" style="width: auto;">
                                        <option value="">{{__('Bulk Actions')}}</option>
                                        <option value="activate">{{__('Activate')}}</option>
                                        <option value="deactivate">{{__('Deactivate')}}</option>
                                        <option value="delete">{{__('Delete')}}</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-secondary">{{__('Apply')}}</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="50"><input type="checkbox" id="select-all"></th>
                                        <th>{{__('Day')}}</th>
                                        <th>{{__('Time')}}</th>
                                        <th>{{__('Capacity')}}</th>
                                        <th>{{__('Price Modifier')}}</th>
                                        <th>{{__('Description')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th width="150">{{__('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{$row->id}}"></td>
                                        <td>
                                            <strong>{{$row->day_name}}</strong><br>
                                            <small class="text-muted">{{__('Day')}} {{$row->day_of_week}}</small>
                                        </td>
                                        <td>
                                            <strong>{{$row->formatted_time}}</strong><br>
                                            <small class="text-muted">{{__('Cutoff')}}: {{$row->booking_cutoff_hours}}h</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{$row->max_guests}} {{__('guests')}}</span>
                                        </td>
                                        <td>
                                            @if($row->price_modifier != 0)
                                                <span class="badge badge-{{$row->price_modifier > 0 ? 'success' : 'warning'}}">
                                                    {{$row->price_modifier > 0 ? '+' : ''}}{{format_money($row->price_modifier)}}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->description)
                                                <span title="{{$row->description}}">{{Str::limit($row->description, 50)}}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{$row->active ? 'success' : 'secondary'}}">
                                                {{$row->active ? __('Active') : __('Inactive')}}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary edit-slot" 
                                                        data-id="{{$row->id}}" 
                                                        data-toggle="modal" 
                                                        data-target="#editTimeSlotModal">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-slot" 
                                                        data-id="{{$row->id}}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    {{$rows->appends(request()->query())->links()}}
                    @else
                    <div class="text-center py-5">
                        <i class="fa fa-clock fa-3x text-muted mb-3"></i>
                        <h5>{{__('No time slots found')}}</h5>
                        <p class="text-muted">{{__('Create your first time slot to get started')}}</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTimeSlotModal">
                            <i class="fa fa-plus"></i> {{__('Add Time Slot')}}
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Time Slot Modal -->
<div class="modal fade" id="addTimeSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{route('tour.admin.time_slots.store', $tour->id)}}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Add Time Slot')}}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('Day of Week')}} <span class="text-danger">*</span></label>
                                <select name="day_of_week" class="form-control" required>
                                    <option value="">{{__('Select Day')}}</option>
                                    @for($i = 1; $i <=7; $i++)
                                        <option value="{{$i}}">
                                            {{['', __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday')][$i]}}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('Max Guests')}} <span class="text-danger">*</span></label>
                                <input type="number" name="max_guests" class="form-control" min="1" max="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('Start Time')}} <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('End Time')}}</label>
                                <input type="time" name="end_time" class="form-control">
                                <small class="form-text text-muted">{{__('Optional - for display purposes')}}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('Price Modifier')}}</label>
                                <input type="number" name="price_modifier" class="form-control" step="0.01">
                                <small class="form-text text-muted">{{__('Amount to add/subtract from base price')}}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('Booking Cutoff (Hours)')}}</label>
                                <input type="number" name="booking_cutoff_hours" class="form-control" value="2" min="0" max="72">
                                <small class="form-text text-muted">{{__('Hours before slot when booking closes')}}</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{__('Description')}}</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="{{__('Optional description for this time slot')}}"></textarea>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="activeCheck" checked>
                        <label class="form-check-label" for="activeCheck">{{__('Active')}}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Create Time Slot')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Time Slot Modal -->
<div class="modal fade" id="editTimeSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Edit Time Slot')}}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Update Time Slot')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#select-all').change(function() {
        $('input[name="ids[]"]').prop('checked', this.checked);
    });

    // Bulk form submission
    $('#bulkForm').submit(function(e) {
        if (!$('select[name="action"]').val()) {
            e.preventDefault();
            alert('{{__("Please select an action")}}');
            return;
        }

        if (!$('input[name="ids[]"]:checked').length) {
            e.preventDefault();
            alert('{{__('Please select at least one item')}}');
            return;
        }

        if ($('select[name="action"]').val() === 'delete') {
            if (!confirm('{{__('Are you sure you want to delete selected time slots?')}}')) {
                e.preventDefault();
                return;
            }
        }
    });

    // Delete slot functionality
    $('.delete-slot').click(function() {
        if (confirm('{{__('Are you sure you want to delete this time slot?')}}')) {
            const slotId = $(this).data('id');
            const form = $('<form>', {
                method: 'POST',
                action: `/admin/tour/time-slots/${slotId}`
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{csrf_token()}}'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_method',
                value: 'DELETE'
            }));
            
            $('body').append(form);
            form.submit();
        }
    });
});
</script>
@endsection('{{__("Please select at least one item")}}');
            return;
        }

        if ($('select[name="action"]').val() === 'delete') {
            if (!confirm('{{__("Are you sure you want to delete selected time slots?")}}')) {
                e.preventDefault();
                return;
            }
        }
    });

    // Edit slot functionality
    $('.edit-slot').click(function() {
        const slotId = $(this).data('id');
        
        // Load slot data via AJAX
        $.get(`/admin/tour/time-slots/${slotId}/edit`, function(data) {
            $('#editModalBody').html(data);
            $('#editForm').attr('action', `/admin/tour/time-slots/${slotId}`);
        });
    });

    // Delete slot functionality
    $('.delete-slot').click(function() {
        if (confirm('{{__("Are you sure you want to delete this time slot?")}}')) {
            const slotId = $(this).data('id');
            const form = $('<form>', {
                method: 'POST',
                action: `/admin/tour/time-slots/${slotId}`
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{csrf_token()}}'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_method',
                value: 'DELETE'
            }));
            
            $('body').append(form);
            form.submit();
        }
    });
});
</script>
@endsection