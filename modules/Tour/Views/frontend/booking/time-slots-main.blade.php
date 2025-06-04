{{-- Time slots are now handled in the booking sidebar for better UX --}}
{{-- This prevents duplication and keeps the interface clean --}}
@if($row->meta && $row->meta->enable_time_slots)
<div class="time-slots-info-section">
    <div class="alert alert-info">
        <i class="fa fa-clock"></i>
        <strong>{{__('Time Slot Selection')}}</strong><br>
        {{__('Please select your preferred time slot in the booking form on the right after choosing a date.')}}
    </div>
</div>

<style>
.time-slots-info-section {
    margin: 20px 0;
}

.time-slots-info-section .alert {
    border-radius: 8px;
    border-left: 4px solid #17a2b8;
    background: #f8f9fa;
    padding: 15px;
}

.time-slots-info-section .alert i {
    color: #17a2b8;
    margin-right: 8px;
    font-size: 16px;
}
</style>

{{-- Time slot functionality is now handled in the booking sidebar --}}
@endif