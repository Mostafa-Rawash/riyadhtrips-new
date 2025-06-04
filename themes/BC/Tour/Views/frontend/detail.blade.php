@extends('layouts.app')
@push('css')
    <link href="{{ asset('dist/frontend/module/tour/css/tour.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/fotorama/fotorama.css") }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/bc/tour/css/tour-time-slots.css?_ver='.config('app.asset_version')) }}"/>
@endpush
@section('content')
    <div class="bravo_detail_tour">
        @include('Layout::parts.bc')
        @if  ($row->category_id  == 9)
        @include('Layout::global.details.gallery')
        @endif
        @if  ($row->category_id  != 9)
        @include('Tour::frontend.layouts.details.tour-banner')
        @endif
        <div class="bravo_content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-lg-8">
                        @php $review_score = $row->review_data @endphp
                        @include('Tour::frontend.layouts.details.tour-detail')
                        @include('Tour::frontend.layouts.details.tour-review')
                    </div>
                    <div class="col-md-12 col-lg-4">
                        @include('Tour::frontend.layouts.details.vendor')
                        @if  ($row->category_id  == 9)
                            @include('Tour::frontend.layouts.details.tour-package-form-book')
                        @elseif ($row->category_id  != 9)
                            @include('Tour::frontend.layouts.details.tour-form-book')
                        @endif
                        @include('Tour::frontend.layouts.details.open-hours')
                    </div>
                </div>
                <div class="row end_tour_sticky">
                    <div class="col-md-12">
                        @include('Tour::frontend.layouts.details.tour-related')
                    </div>
                </div>
            </div>
        </div>
        <div class="bravo-more-book-mobile">
            <div class="container">
                <div class="left">
                    <div class="g-price">
                        <div class="prefix">
                            <span class="fr_text">{{__("from")}}</span>
                        </div>
                        <div class="price">
                            <span class="onsale">{{ $row->display_sale_price }}</span>
                            <span class="text-price">{{ $row->display_price }}</span>
                        </div>
                    </div>
                    @if(setting_item('tour_enable_review'))
                    <?php
                    $reviewData = $row->getScoreReview();
                    $score_total = $reviewData['score_total'];
                    ?>
                    <div class="service-review tour-review-{{$score_total}}">
                        <div class="list-star">
                            <ul class="booking-item-rating-stars">
                                <li><i class="fa fa-star-o"></i></li>
                                <li><i class="fa fa-star-o"></i></li>
                                <li><i class="fa fa-star-o"></i></li>
                                <li><i class="fa fa-star-o"></i></li>
                                <li><i class="fa fa-star-o"></i></li>
                            </ul>
                            <div class="booking-item-rating-stars-active" style="width: {{  $score_total * 2 * 10 ?? 0  }}%">
                                <ul class="booking-item-rating-stars">
                                    <li><i class="fa fa-star"></i></li>
                                    <li><i class="fa fa-star"></i></li>
                                    <li><i class="fa fa-star"></i></li>
                                    <li><i class="fa fa-star"></i></li>
                                    <li><i class="fa fa-star"></i></li>
                                </ul>
                            </div>
                        </div>
                        <span class="review">
                        @if($reviewData['total_review'] > 1)
                                {{ __(":number Reviews",["number"=>$reviewData['total_review'] ]) }}
                            @else
                                {{ __(":number Review",["number"=>$reviewData['total_review'] ]) }}
                            @endif
                    </span>
                    </div>
                    @endif
                </div>
                <div class="right">
                    @if($row->getBookingEnquiryType() === "book")
                        <a class="btn btn-primary bravo-button-book-mobile">{{__("Book Now")}}</a>
                    @else
                        <a class="btn btn-primary" data-toggle="modal" data-target="#enquiry_form_modal">{{__("Contact Now")}}</a>
                   @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {!! App\Helpers\MapEngine::scripts() !!}
    <script>
        jQuery(function ($) {
            @if($row->map_lat && $row->map_lng)
            new BravoMapEngine('map_content', {
                disableScripts: true,
                fitBounds: true,
                center: [{{$row->map_lat}}, {{$row->map_lng}}],
                zoom:{{$row->map_zoom ?? "8"}},
                ready: function (engineMap) {
                    engineMap.addMarker([{{$row->map_lat}}, {{$row->map_lng}}], {
                        icon_options: {
                            iconUrl:"{{get_file_url(setting_item("tour_icon_marker_map"),'full') ?? url('images/icons/png/pin.png') }}"
                        }
                    });
                }
            });
            @endif
        })
    </script>
    <script>
        var bravo_booking_data = {!! json_encode($booking_data) !!}
        var bravo_booking_i18n = {
                no_date_select:'{{__('Please select Start date')}}',
                no_guest_select:'{{__('Please select at least one guest')}}',
                load_dates_url:'{{route('tour.vendor.availability.loadDates')}}',
                load_time_slots_url:'{{ route("tour.api.time_slots.available") }}',
                name_required:'{{ __("Name is Required") }}',
                email_required:'{{ __("Email is Required") }}',
            };
    </script>
    <script type="text/javascript" src="{{ asset("libs/ion_rangeslider/js/ion.rangeSlider.min.js") }}"></script>
    <script type="text/javascript" src="{{ asset("libs/fotorama/fotorama.js") }}"></script>
    <script type="text/javascript" src="{{ asset("libs/sticky/jquery.sticky.js") }}"></script>
    <script type="text/javascript" src="{{ asset('module/tour/js/single-tour.js?_ver='.config('app.asset_version')) }}"></script>
@endpush

@if  ($row->category_id  == 9)
@push('css')
<style>
    .fotorama {
        width: 100%;
        height: 675px;
        position: relative;
    }

    img.fotorama__img {
        top: 0px !important;
        left: 0px !important;
        width: 100% !important;
        max-height: 100% !important;
        height: 100% !important;
        object-fit: cover;
    }

    .fotorama__wrap.fotorama__wrap--css3.fotorama__wrap--slide.fotorama__wrap--toggle-arrows {
        height: 100%;
    }

    .fotorama__nav-wrap {
        position: absolute;
        bottom: 0px;
        justify-self: anchor-center;    
        text-align-last: left;
        display: block;
        /* width: 100%; */
        /* padding-right: 15px; */
        /* padding-left: 15px; */
    }

    .fotorama__stage__shaft {
        height: 100% !important;
    }

    .fotorama__stage__shaft {
        min-height: 100% !important;
    }

    .fotorama__nav--thumbs .fotorama__nav__frame,
    .fotorama__thumb-border,
    .fotorama__nav__frame .fotorama__img {
        border-radius: 8px !important;
        cursor: pointer;
    }

    .fotorama__thumb-border{
        display:none;
    }
    .social-share,
    .fotorama__fullscreen-icon {
        display: none;
    }

    /*.fotorama__stage.fotorama__pointer {*/
    /*    max-height: 100%;*/
    /*}*/

    .fotorama__nav__shaft , .responsive-title{
        /* width: 100% !important; */
        /* margin: auto; */
        /* overflow: hidden; */
    }

    @media (min-width: 992px) {
        .fotorama__nav__shaft , .responsive-title{
            -ms-flex: 0 0 66.666667%;
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }
    }



    /* Bootstrap-like container for fotorama__nav-wrap */
    .fotorama__nav-wrap {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }

    /* Responsive breakpoints similar to Bootstrap containers */
    @media (min-width: 576px) {
        .fotorama__nav-wrap {
            max-width: 540px;
        }
    }

    @media (min-width: 768px) {
        .fotorama__nav-wrap {
            max-width: 720px;
        }
    }

    @media (min-width: 992px) {
        .fotorama__nav-wrap {
            max-width: 960px;
        }
    }

    @media (min-width: 1200px) {
        .fotorama__nav-wrap {
            max-width: 1140px;
        }
    }

    /* Bootstrap 5 adds this larger breakpoint */
    @media (min-width: 1400px) {
        .fotorama__nav-wrap {
            max-width: 1320px;
        }
    }


    /* Bootstrap-like row for fotorama__nav-wrap */
    .fotorama__nav-wrap {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
        box-sizing: border-box;
        margin: auto;
    }

    /* Optional: Add Bootstrap 5's row utility classes */

    /* No gutters */
    .fotorama__nav-wrap--no-gutters {
        margin-right: 0;
        margin-left: 0;
    }

    .fotorama__nav-wrap--no-gutters>* {
        padding-right: 0;
        padding-left: 0;
    }

    /* Responsive gutters */
    @media (min-width: 576px) {
        .fotorama__nav-wrap--gx-sm-1 {
            margin-right: -0.25rem;
            margin-left: -0.25rem;
        }

        .fotorama__nav-wrap--gx-sm-1>* {
            padding-right: 0.25rem;
            padding-left: 0.25rem;
        }

        /* Additional gutter classes (gx-sm-2, gx-sm-3, etc.) can be added similarly */
    }

    /* Vertical gutters */
    .fotorama__nav-wrap--gy-0>* {
        margin-top: 0;
    }

    .fotorama__nav-wrap--gy-1>* {
        margin-top: 0.25rem;
    }

    .fotorama__nav-wrap--gy-2>* {
        margin-top: 0.5rem;
    }

    .fotorama__nav-wrap--gy-3>* {
        margin-top: 1rem;
    }

    /* Align content utilities */
    .fotorama__nav-wrap--justify-content-start {
        justify-content: flex-start;
    }

    .fotorama__nav-wrap--justify-content-end {
        justify-content: flex-end;
    }

    .fotorama__nav-wrap--justify-content-center {
        justify-content: center;
    }

    .fotorama__nav-wrap--justify-content-between {
        justify-content: space-between;
    }

    .fotorama__nav-wrap--justify-content-around {
        justify-content: space-around;
    }

    .fotorama__nav-wrap--justify-content-evenly {
        justify-content: space-evenly;
    }

    /* Align items utilities */
    .fotorama__nav-wrap--align-items-start {
        align-items: flex-start;
    }

    .fotorama__nav-wrap--align-items-end {
        align-items: flex-end;
    }

    .fotorama__nav-wrap--align-items-center {
        align-items: center;
    }

    .fotorama__nav-wrap--align-items-baseline {
        align-items: baseline;
    }

    .fotorama__nav-wrap--align-items-stretch {
        align-items: stretch;
    }




    /* Bootstrap-like column for fotorama__nav__shaft */
    .fotorama__nav__shaft , .responsive-title{
        position: relative;
        /* width: 100%; */
        /* padding-right: 15px; */
        /* padding-left: 15px; */
        box-sizing: border-box;
    }

    /* Default behavior for smaller screens (mobile first) */
    .fotorama__nav__shaft , .responsive-title{
        flex: 0 0 100%;
        /* max-width: 100%; */
    }

    /* Medium screens (md): Full width (col-md-12) */
    @media (min-width: 768px) {
        .fotorama__nav__shaft , .responsive-title{
            flex: 0 0 100%;
            /* max-width: 100%; */
        }
    }

    /* Large screens (lg): 8/12 columns (col-lg-8) */
    @media (min-width: 992px) {
        .fotorama__nav__shaft , .responsive-title{
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }
    }

    /* Optional: Add Bootstrap column utilities */
    .fotorama__nav__shaft--offset-lg-2 {
        margin-left: 16.666667%;
    }

    /* Center the column if needed */
    .fotorama__nav__shaft--mx-auto {
        margin-left: auto;
        margin-right: auto;
    }

    /* Black to transparent gradient overlay for Fotorama stage */
    .fotorama__stage {
        position: relative;
        /* Ensure positioning context if not already set */
    }

    .fotorama__stage::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top,
                rgba(0, 0, 0, 1) 0%,
                rgba(0, 0, 0, 0.8) 20%,
                rgba(0, 0, 0, 0.6) 40%,
                rgba(0, 0, 0, 0.4) 60%,
                rgba(0, 0, 0, 0.2) 80%,
                rgba(0, 0, 0, 0) 100%);
        pointer-events: none;
        /* Allows clicks to pass through to the Fotorama controls */
        z-index: 2;
        /* Make sure it's above the images but below any controls */
    }





    .responsive-title {
        color:white;
        font-weight: 900;
        word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    hyphens: auto;
    display: block;
    text-align: center;
    max-width: 100%;
    width: 100%;    }

    .fotorama__stage {
    height: 100% !important;
}

.fotorama__stage__shaft {
    height: 100%;
}

.fotorama__stage__frame {
    height: 100%;
}

img.fotorama__img {
    height: 100%;
}

</style>

@endpush

@push('js')
<script defer>
window.onload = function() {
    // Find the element
    const fotoramaNav = document.querySelector('.fotorama__nav__shaft');

    // Check if it exists
    if (fotoramaNav) {
        // Create and insert the heading with Blade syntax for the variable
        const titleHTML = `<h1 class="responsive-title">
    {{ $translation->title }}</h1>`;
        fotoramaNav.insertAdjacentHTML('beforebegin', titleHTML);
        console.log('Title has been inserted');
    } else {
        console.error('Element .fotorama__nav__shaft , .responsive-titlenot found');
    }
};
</script>
@endpush

@endif
@push('js')
    
    <!-- GUARANTEED TIME SLOTS FIX -->
    <script>
        console.log('🚀 GUARANTEED Time Slots Fix Loading...');
        
        function guaranteedTimeSlotsFix() {
            const vueApp = document.getElementById('bravo_tour_book_app')?.__vue__;
            
            if (!vueApp) {
                console.log('⏳ Vue app not ready, retrying in 500ms...');
                setTimeout(guaranteedTimeSlotsFix, 500);
                return;
            }
            
            console.log('✅ Vue app found! Applying guaranteed fix...');
            
            // Force enable time slots
            Vue.set(vueApp, 'enable_time_slots', true);
            Vue.set(vueApp, 'available_time_slots', []);
            Vue.set(vueApp, 'selected_time_slot', null);
            Vue.set(vueApp, 'loading_time_slots', false);
            Vue.set(vueApp, 'sold_out_slots', []);
            Vue.set(vueApp, 'show_sold_out_slots', false);
            Vue.set(vueApp, 'time_slot_id', null);
            Vue.set(vueApp, 'start_time', null);
            
            // Add missing methods
            vueApp.getGuestCount = function() {
                if (this.person_types && this.person_types.length) {
                    return this.person_types.reduce((total, type) => total + (parseInt(type.number) || 0), 0);
                }
                return parseInt(this.guests) || 1;
            };
            
            vueApp.formatTime = function(time) {
                if (!time) return '';
                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minutes} ${ampm}`;
            };
            
            vueApp.selectTimeSlot = function(slot) {
                console.log('🎯 GUARANTEED: Time slot selected:', slot);
                this.selected_time_slot = slot;
                this.time_slot_id = slot.id;
                this.start_time = slot.start_time;
            };
            
            vueApp.clearTimeSlot = function() {
                this.selected_time_slot = null;
                this.time_slot_id = null;
                this.start_time = null;
            };
            
            vueApp.loadTimeSlots = function(date) {
                console.log('📡 GUARANTEED: Loading time slots for date:', date);
                
                if (!date) {
                    console.warn('No date provided');
                    return;
                }
                
                this.loading_time_slots = true;
                this.available_time_slots = [];
                this.sold_out_slots = [];
                
                const guests = this.getGuestCount();
                
                console.log('📊 Request params:', {
                    tour_id: this.id,
                    date: date,
                    guests: guests,
                    url: bravo_booking_i18n.load_time_slots_url
                });
                
                $.ajax({
                    url: bravo_booking_i18n.load_time_slots_url,
                    method: 'GET',
                    data: {
                        tour_id: this.id,
                        date: date,
                        guests: guests
                    },
                    success: (response) => {
                        console.log('✅ GUARANTEED: API Success:', response);
                        this.loading_time_slots = false;
                        
                        if (response.success && response.data && response.data.time_slots) {
                            const allSlots = response.data.time_slots;
                            
                            this.available_time_slots = allSlots.filter(slot => 
                                !slot.is_sold_out && (slot.remaining_capacity >= guests)
                            );
                            
                            this.sold_out_slots = allSlots.filter(slot => 
                                slot.is_sold_out || (slot.remaining_capacity < guests)
                            );
                            
                            console.log('✅ GUARANTEED: Slots processed:', {
                                total: allSlots.length,
                                available: this.available_time_slots.length,
                                sold_out: this.sold_out_slots.length
                            });
                        } else {
                            console.warn('⚠️ GUARANTEED: Invalid response format');
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('❌ GUARANTEED: API Error:', {
                            status: xhr.status,
                            error: error,
                            response: xhr.responseJSON
                        });
                        this.loading_time_slots = false;
                    }
                });
            };
            
            // Watch for date changes
            vueApp.$watch('start_date', function(newDate) {
                console.log('📅 GUARANTEED: Date watcher triggered:', newDate);
                if (newDate && this.enable_time_slots) {
                    setTimeout(() => {
                        this.loadTimeSlots(newDate);
                    }, 200);
                }
            });
            
            // Make globally available for debugging
            window.bravo_booking_vue = vueApp;
            window.testTimeSlots = function() {
                console.log('🧪 Manual test triggered');
                vueApp.loadTimeSlots('2025-06-02');
            };
            
            console.log('✅ GUARANTEED Time slots fix applied successfully!');
            console.log('🎯 Current state:', {
                enable_time_slots: vueApp.enable_time_slots,
                tour_id: vueApp.id,
                current_date: vueApp.start_date
            });
            console.log('🧪 Test commands: testTimeSlots() or debugTimeSlots()');
        }
        
        // Start the guaranteed fix
        setTimeout(guaranteedTimeSlotsFix, 2000);
    </script>
@endpush
