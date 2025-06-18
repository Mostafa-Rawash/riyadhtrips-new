<div class="time-slots-section">
    <div class="form-group">
        <label>
        <input type="checkbox" name="enable_time_slots" value="1" @if($row->meta && $row->meta->enable_time_slots) checked @endif>
            {{__('Enable specific time slots for this tour')}}
        </label>
        <small class="form-text text-muted">{{__('When enabled, customers must select a specific time slot when booking this tour.')}}</small>
    </div>
    
    <div id="time-slots-container" @if(!($row->meta && $row->meta->enable_time_slots) && !($row->timeSlots && $row->timeSlots->count() > 0)) style="display: none" @endif>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            {{__('Define available time slots for each day of the week. Each time slot has its own capacity.')}}
        </div>
        
        <div class="time-slots-by-day">
            @foreach(range(1, 7) as $day)
                @php
                    $dayNames = [
                        1 => __('Monday'),
                        2 => __('Tuesday'), 
                        3 => __('Wednesday'),
                        4 => __('Thursday'),
                        5 => __('Friday'),
                        6 => __('Saturday'),
                        7 => __('Sunday')
                    ];
                    $dayName = $dayNames[$day];
                    $slots = isset($row->id) ? $row->getTimeSlotsForDay($day) : collect([]);
                @endphp
                <div class="day-time-slots card mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $dayName }}</h5>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input day-enabled" 
                                       id="day-enabled-{{ $day }}" 
                                       data-day="{{ $day }}" 
                                       {{ $slots->count() ? 'checked' : '' }}>
                                <label class="form-check-label" for="day-enabled-{{ $day }}">
                                    {{ __('Enable this day') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body time-slots-list" id="time-slots-day-{{ $day }}" 
                         {{ $slots->count() ? '' : 'style="display: none"' }}>
                        
                        <div class="time-slots-wrapper">
                            @if($slots->count())
                                @foreach($slots as $index => $slot)
                                    <div class="time-slot-item row mb-2">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('Start Time')}}</label>
                                                <input type="time" class="form-control" 
                                                       name="time_slots[{{ $day }}][{{ $index }}][start_time]" 
                                                       value="{{ $slot->start_time }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('Max Guests')}}</label>
                                                <input type="number" class="form-control" 
                                                       name="time_slots[{{ $day }}][{{ $index }}][max_guests]" 
                                                       value="{{ $slot->max_guests }}" min="1" max="999">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm remove-time-slot form-control">
                                                    <i class="fa fa-trash"></i> {{__('Remove')}}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <button type="button" class="btn btn-primary add-time-slot" data-day="{{ $day }}">
                            <i class="fa fa-plus"></i> {{ __('Add Time Slot') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('js')
<script>
jQuery(function($) {
    // Toggle time slots section
    $('input[name="enable_time_slots"]').change(function() {
        if ($(this).is(':checked')) {
            $('#time-slots-container').show();
        } else {
            $('#time-slots-container').hide();
        }
    });
    
    // Toggle day slots
    $('.day-enabled').change(function() {
        var day = $(this).data('day');
        if ($(this).is(':checked')) {
            $('#time-slots-day-' + day).show();
            // Add a default time slot if none exist
            if ($('#time-slots-day-' + day + ' .time-slot-item').length === 0) {
                addTimeSlot(day);
            }
        } else {
            $('#time-slots-day-' + day).hide();
            // Remove all time slots for this day
            $('#time-slots-day-' + day + ' .time-slot-item').remove();
        }
    });
    
    // Add time slot function
    function addTimeSlot(day) {
        var slotsList = $('#time-slots-day-' + day + ' .time-slots-wrapper');
        var index = slotsList.find('.time-slot-item').length;
        
        var html = `
            <div class="time-slot-item row mb-2">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>{{__('Start Time')}}</label>
                        <input type="time" class="form-control" name="time_slots[${day}][${index}][start_time]" value="09:00">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>{{__('Max Guests')}}</label>
                        <input type="number" class="form-control" name="time_slots[${day}][${index}][max_guests]" value="10" min="1" max="999">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm remove-time-slot form-control">
                            <i class="fa fa-trash"></i> {{__('Remove')}}
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        slotsList.append(html);
    }
    
    // Add time slot button click
    $('.add-time-slot').click(function() {
        var day = $(this).data('day');
        addTimeSlot(day);
    });
    
    // Remove time slot (using event delegation)
    $(document).on('click', '.remove-time-slot', function() {
        $(this).closest('.time-slot-item').remove();
    });
    
    // Update time slot indices when slots are added/removed
    function updateTimeSlotIndices(day) {
        $('#time-slots-day-' + day + ' .time-slot-item').each(function(index) {
            $(this).find('input[name*="[start_time]"]').attr('name', 'time_slots[' + day + '][' + index + '][start_time]');
            $(this).find('input[name*="[max_guests]"]').attr('name', 'time_slots[' + day + '][' + index + '][max_guests]');
        });
    }
    
    // Update indices when slots are removed
    $(document).on('click', '.remove-time-slot', function() {
        var day = $(this).closest('.time-slots-list').attr('id').replace('time-slots-day-', '');
        setTimeout(function() {
            updateTimeSlotIndices(day);
        }, 100);
    });
});
</script>
@endpush