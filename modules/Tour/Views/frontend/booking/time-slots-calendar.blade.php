{{-- Calendar View for Time Slots --}}
<div class="time-slots-calendar-view">
    <div class="calendar-navigation">
        <button class="btn btn-sm btn-outline-secondary" @click="previousWeek">
            <i class="fa fa-chevron-left"></i>
        </button>
        <h5 class="calendar-week-title">@{{ currentWeekTitle }}</h5>
        <button class="btn btn-sm btn-outline-secondary" @click="nextWeek">
            <i class="fa fa-chevron-right"></i>
        </button>
    </div>
    
    <div class="calendar-week-view">
        <div class="calendar-day" 
             v-for="day in weekDays" 
             :key="day.date"
             :class="{
                 'selected': day.date === start_date,
                 'disabled': !day.hasSlots,
                 'today': day.isToday
             }"
             @click="selectCalendarDate(day)">
            <div class="day-header">
                <div class="day-name">@{{ day.dayName }}</div>
                <div class="day-number">@{{ day.dayNumber }}</div>
            </div>
            <div class="day-slots" v-if="day.slots && day.slots.length">
                <div class="slot-preview" 
                     v-for="(slot, index) in day.slots.slice(0, 3)" 
                     :key="slot.id"
                     :class="{'sold-out': slot.is_sold_out}">
                    @{{ slot.formatted_time }}
                </div>
                <div class="more-slots" v-if="day.slots.length > 3">
                    +@{{ day.slots.length - 3 }} {{__("more")}}
                </div>
            </div>
            <div class="no-slots" v-else>
                {{__("No tours")}}
            </div>
        </div>
    </div>
    
    <div class="selected-day-slots" v-if="selectedDaySlots.length > 0">
        <h5 class="selected-day-title">
            {{__("Available times for")}} @{{ selectedDayTitle }}
        </h5>
        <div class="time-slots-grid">
            <div class="time-slot-item" 
                 v-for="slot in selectedDaySlots" 
                 :key="slot.id"
                 :class="{
                     'selected': selected_time_slot == slot.id,
                     'disabled': slot.is_sold_out || slot.remaining_capacity < total_guests,
                     'limited': slot.remaining_capacity > 0 && slot.remaining_capacity <= 5
                 }"
                 @click="selectTimeSlot(slot)">
                <div class="time-slot-time">@{{ slot.formatted_time }}</div>
                <div class="time-slot-status">
                    <span v-if="slot.is_sold_out" class="text-danger">
                        {{__("Sold Out")}}
                    </span>
                    <span v-else-if="slot.remaining_capacity <= 5" class="text-warning">
                        @{{ slot.remaining_capacity }} {{__("left")}}
                    </span>
                    <span v-else class="text-success">
                        {{__("Available")}}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.time-slots-calendar-view {
    padding: 15px;
}

.calendar-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.calendar-week-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.calendar-week-view {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}

.calendar-day {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
    min-height: 120px;
}

.calendar-day:hover:not(.disabled) {
    border-color: #1EC69A;
    box-shadow: 0 2px 8px rgba(30, 198, 154, 0.2);
}

.calendar-day.selected {
    border-color: #1EC69A;
    background-color: #f0faf8;
}

.calendar-day.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f8f8f8;
}

.calendar-day.today .day-header {
    background-color: #e8f5f2;
    margin: -10px -10px 10px;
    padding: 8px 10px;
    border-radius: 6px 6px 0 0;
}

.day-header {
    text-align: center;
    margin-bottom: 8px;
}

.day-name {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.day-number {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.day-slots {
    font-size: 11px;
}

.slot-preview {
    background-color: #e8f5f2;
    padding: 2px 6px;
    margin-bottom: 3px;
    border-radius: 3px;
    color: #1EC69A;
}

.slot-preview.sold-out {
    background-color: #ffebee;
    color: #dc3545;
    text-decoration: line-through;
}

.more-slots {
    color: #666;
    font-style: italic;
    text-align: center;
    margin-top: 3px;
}

.no-slots {
    text-align: center;
    color: #999;
    font-style: italic;
    margin-top: 10px;
}

.selected-day-title {
    font-size: 16px;
    margin-bottom: 15px;
    color: #333;
}

.selected-day-slots .time-slots-grid {
    max-height: 300px;
    overflow-y: auto;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .calendar-week-view {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .calendar-day {
        min-height: 100px;
        padding: 8px;
    }
    
    .day-number {
        font-size: 16px;
    }
    
    .slot-preview {
        font-size: 10px;
    }
}

@media (max-width: 480px) {
    .calendar-week-view {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
// Add calendar functionality to Vue app
if (window.bravo_booking_vue) {
    // Calendar data properties
    Vue.set(window.bravo_booking_vue, 'currentWeekStart', null);
    Vue.set(window.bravo_booking_vue, 'weekDays', []);
    Vue.set(window.bravo_booking_vue, 'selectedDaySlots', []);
    Vue.set(window.bravo_booking_vue, 'selectedDayTitle', '');
    Vue.set(window.bravo_booking_vue, 'currentWeekTitle', '');
    Vue.set(window.bravo_booking_vue, 'calendarSlots', {});
    
    // Initialize calendar
    window.bravo_booking_vue.initCalendar = function() {
        const today = new Date();
        const startOfWeek = new Date(today);
        startOfWeek.setDate(today.getDate() - today.getDay());
        this.currentWeekStart = startOfWeek;
        this.loadWeekData();
    };
    
    // Load week data
    window.bravo_booking_vue.loadWeekData = function() {
        const days = [];
        const weekStart = new Date(this.currentWeekStart);
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        
        // Format week title
        const monthNames = window.bravo_booking_i18n.month_names || ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const startMonth = monthNames[weekStart.getMonth()];
        const endMonth = monthNames[weekEnd.getMonth()];
        
        if (startMonth === endMonth) {
            this.currentWeekTitle = `${startMonth} ${weekStart.getDate()} - ${weekEnd.getDate()}, ${weekStart.getFullYear()}`;
        } else {
            this.currentWeekTitle = `${startMonth} ${weekStart.getDate()} - ${endMonth} ${weekEnd.getDate()}, ${weekEnd.getFullYear()}`;
        }
        
        // Generate days
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(weekStart.getDate() + i);
            
            const dateStr = this.formatDateForAPI(date);
            const dayData = {
                date: dateStr,
                dayName: this.getDayName(date),
                dayNumber: date.getDate(),
                isToday: this.isToday(date),
                hasSlots: false,
                slots: []
            };
            
            // Check if we have slots for this date
            if (this.calendarSlots[dateStr]) {
                dayData.slots = this.calendarSlots[dateStr];
                dayData.hasSlots = dayData.slots.length > 0;
            }
            
            days.push(dayData);
        }
        
        this.weekDays = days;
        
        // Load slots for the week
        this.loadWeekSlots();
    };
    
    // Load slots for the entire week
    window.bravo_booking_vue.loadWeekSlots = function() {
        const promises = [];
        
        this.weekDays.forEach(day => {
            if (!this.calendarSlots[day.date]) {
                promises.push(this.loadSlotsForDate(day.date));
            }
        });
        
        if (promises.length > 0) {
            Promise.all(promises).then(() => {
                this.updateWeekDaysSlots();
            });
        }
    };
    
    // Load slots for a specific date
    window.bravo_booking_vue.loadSlotsForDate = function(date) {
        return new Promise((resolve) => {
            jQuery.ajax({
                url: bookingCore.routes.tour_time_slots_availability || '/api/tour/time-slots/available',
                method: 'GET',
                data: {
                    tour_id: this.id,
                    date: date,
                    guest_count: this.getTotalGuests()
                },
                success: (response) => {
                    if (response.data) {
                        this.calendarSlots[date] = response.data;
                    } else {
                        this.calendarSlots[date] = [];
                    }
                    resolve();
                },
                error: () => {
                    this.calendarSlots[date] = [];
                    resolve();
                }
            });
        });
    };
    
    // Update week days with loaded slots
    window.bravo_booking_vue.updateWeekDaysSlots = function() {
        this.weekDays = this.weekDays.map(day => {
            if (this.calendarSlots[day.date]) {
                day.slots = this.calendarSlots[day.date];
                day.hasSlots = day.slots.length > 0;
            }
            return day;
        });
    };
    
    // Navigate to previous week
    window.bravo_booking_vue.previousWeek = function() {
        const prev = new Date(this.currentWeekStart);
        prev.setDate(prev.getDate() - 7);
        this.currentWeekStart = prev;
        this.loadWeekData();
    };
    
    // Navigate to next week
    window.bravo_booking_vue.nextWeek = function() {
        const next = new Date(this.currentWeekStart);
        next.setDate(next.getDate() + 7);
        this.currentWeekStart = next;
        this.loadWeekData();
    };
    
    // Select a date from calendar
    window.bravo_booking_vue.selectCalendarDate = function(day) {
        if (!day.hasSlots) return;
        
        this.start_date = day.date;
        this.start_date_html = this.formatDateDisplay(new Date(day.date));
        this.selectedDaySlots = day.slots;
        this.selectedDayTitle = `${day.dayName}, ${this.formatDateDisplay(new Date(day.date))}`;
        
        // Update main time slots
        this.time_slots = day.slots;
        
        // Trigger date change event
        if (this.$refs.start_date) {
            jQuery(this.$refs.start_date).trigger('dateSelected', [day.date]);
        }
    };
    
    // Helper functions
    window.bravo_booking_vue.formatDateForAPI = function(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    window.bravo_booking_vue.formatDateDisplay = function(date) {
        return date.toLocaleDateString(window.bravo_booking_i18n.locale || 'en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };
    
    window.bravo_booking_vue.getDayName = function(date) {
        const dayNames = window.bravo_booking_i18n.day_names || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return dayNames[date.getDay()];
    };
    
    window.bravo_booking_vue.isToday = function(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    };
    
    // Initialize calendar when component is ready
    setTimeout(() => {
        if (window.bravo_booking_vue && window.bravo_booking_vue.enable_time_slots) {
            window.bravo_booking_vue.initCalendar();
        }
    }, 1000);
}
</script>