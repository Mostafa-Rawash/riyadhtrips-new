<div class="bravo_single_book_wrap @if(setting_item('tour_enable_inbox')) has-vendor-box @endif">



    <div class="bravo_single_book">



        <div id="bravo_tour_book_app" v-cloak>



            @if($row->discount_percent)



            <div class="tour-sale-box">



                <span class="sale_class box_sale sale_small">{{$row->discount_percent}}</span>



            </div>



            @endif



            <div class="form-head">



                <div class="price">



                    <span class="label">



                        {{__("from")}}



                    </span>



                    <span class="value">



                        <span class="onsale">{{ $row->display_sale_price }}</span>



                        <span class="text-lg">{{ $row->display_price }}</span>



                    </span>



                </div>



            </div>



            <!-- <div class="nav-enquiry" v-if="is_form_enquiry_and_book"> -->
            <div class="nav-enquiry">
                <div class="enquiry-item" :class="{active: enquiry_type=='book'}" @click="enquiry_type='book'">
                    <span>{{ __("Book") }}</span>
                </div>
                <div class="enquiry-item " :class="{active: enquiry_type=='enquiry'}" @click="enquiry_type='enquiry'">
                    <span>{{ __("Enquiry") }}</span>
                </div>
            </div>



            <div class="form-book" :class="{'d-none':enquiry_type!='book'}">



                <div class="form-content">



                    <div class="form-group form-date-field form-date-search clearfix " data-format="{{get_moment_date_format()}}">



                        <div class="d-flex p-2  flex-wrap clearfix text-center" v-if="is_fixed_date">



                            <div class="w-50 py-3 flex-grow-1">



                                <div class="font-weight-bold">{{__("Tour Start Date")}}</div>



                                <span>@{{ start_date_html }}</span>



                            </div>



                            <div class="w-50 py-3 flex-grow-1 border-left">



                                <div class="font-weight-bold">{{__("Tour End Date")}}</div>



                                <span>@{{ end_date_html }}</span>



                            </div>



                            <div class="w-100 py-3 flex-grow-1 border-top">



                                <div class="font-weight-bold">{{__("Last Booking Date")}}</div>



                                <span>@{{ last_booking_date_html }}</span>



                            </div>



                        </div>



                        <div class="date-wrapper clearfix" @click="openStartDate" v-else>



                            <div class="check-in-wrapper">



                                <label>{{__("Start Date")}}</label>



                                <div class="render check-in-render">@{{start_date_html}}</div>



                            </div>



                            <i class="fa fa-angle-down arrow"></i>



                        </div>



                        <input type="text" class="start_date" ref="start_date" style="height: 1px; visibility: hidden">



                    </div>



                    <div class="" v-if="person_types">



                        <div class="form-group form-guest-search" v-for="(type,index) in person_types">



                            <div class="guest-wrapper d-flex justify-content-between align-items-center">



                                <div class="flex-grow-1">



                                    <label>@{{type.name}}</label>



                                    <div class="render check-in-render">@{{type.desc}}</div>



                                    <div class="render check-in-render">@{{type.display_price}} {{__("per person")}}</div>



                                </div>



                                <div class="flex-shrink-0">



                                    <div class="input-number-group">



                                        <i class="icon ion-ios-remove-circle-outline" @click="minusPersonType(type)"></i>



                                        <span class="input"><input type="number" v-model="type.number" min="1" @change="changePersonType(type)" /></span>



                                        <i class="icon ion-ios-add-circle-outline" @click="addPersonType(type)"></i>



                                    </div>



                                </div>



                            </div>



                        </div>



                    </div>

                    <div class="form-group form-guest-search" v-else>



                        <div class="guest-wrapper d-flex justify-content-between align-items-center">



                            <div class="flex-grow-1">



                                <label>{{__("Guests")}}</label>



                            </div>



                            <div class="flex-shrink-0">



                                <div class="input-number-group">



                                    <i class="icon ion-ios-remove-circle-outline" @click="minusGuestsType()"></i>



                                    <span class="input"><input type="number" v-model="guests" min="0" /></span>



                                    <i class="icon ion-ios-add-circle-outline" @click="addGuestsType()"></i>



                                </div>



                            </div>



                        </div>



                    </div>

                    <div class="form-section-group form-group" v-if="packages.length">
                        <h4 class="form-section-title">{{ __('Packages:') }}</h4>

                        <div class="form-group" v-for="(type, index) in packages" :key="index">
                            <div class="extra-price-wrap d-flex justify-content-between">
                                <!-- Checkbox and Package Name -->
                                <div class="flex-grow-1">
                                    <label>
                                        <input type="radio" name="package" value="0" v-model="type.enable">
                                        @{{ type.name }}
                                    </label>
                                    <div class="render" v-if="type.price">(@{{ type.display_price }})</div>
                                </div>

                                <!-- Start Times as Radio Buttons -->
                                <div class="flex-shrink-0">
                                    <div v-if="type.start_times && type.start_times.length">
                                        <div v-for="(time, timeIndex) in type.start_times" :key="timeIndex">
                                            <label>
                                                <input
                                                    type="radio"
                                                    :name="'start_time_' + index"
                                                    :value="time"
                                                    v-model="type.selected_time">
                                                @{{ time }}
                                            </label>
                                        </div>
                                    </div>
                                    <div v-else>
                                        {{ __('No start times available') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-group form-group" v-if="extra_price.length">



                        <h4 class="form-section-title">{{__('Extra prices:')}}</h4>



                        <div class="form-group" v-for="(type,index) in extra_price">



                            <div class="extra-price-wrap d-flex justify-content-between">



                                <div class="flex-grow-1">



                                    <label><input type="checkbox" true-value="1" false-value="0" v-model="type.enable"> @{{type.name}}</label>



                                    <div class="render" v-if="type.price_type">(@{{type.price_type}})</div>



                                </div>



                                <div class="flex-shrink-0">@{{type.price_html}}</div>



                            </div>



                        </div>



                    </div>



                    <div class="form-section-group form-group" v-if="discount_by_people_output.length">



                        <h4 class="form-section-title">{{__('Discounts:')}}</h4>



                        <div class="extra-price-wrap d-flex justify-content-between" v-for="(type,index) in discount_by_people_output">



                            <div class="flex-grow-1">



                                <span class="render" v-if='type.to'>



                                    @{{type.from}} - @{{type.to}} {{__('guests')}}



                                </span>



                                <span class="render" v-else>



                                    {{__('from')}} @{{type.from}} {{__('guests')}}



                                </span>



                            </div>



                            <div class="flex-shrink-0">



                                <div class="unit">



                                    - @{{ formatMoney(type.total) }}



                                </div>



                            </div>



                        </div>



                    </div>



                    <div class="form-section-group form-group-padding" v-if="buyer_fees.length">



                        <div class="extra-price-wrap d-flex justify-content-between" v-for="(type,index) in buyer_fees">



                            <div class="flex-grow-1">



                                <label>@{{type.type_name}}



                                    <i class="icofont-info-circle" v-if="type.desc" data-toggle="tooltip" data-placement="top" :title="type.type_desc"></i>



                                </label>



                                <div class="render" v-if="type.price_type">(@{{type.price_type}})</div>



                            </div>



                            <div class="flex-shrink-0">



                                <div class="unit" v-if='type.unit == "percent"'>



                                    @{{ type.price }}%



                                </div>



                                <div class="unit" v-else>



                                    @{{ formatMoney(type.price) }}



                                </div>



                            </div>



                        </div>



                    </div>



                </div>



                <ul class="form-section-total list-unstyled" v-if="total_price > 0">



                    <li>



                        <label>{{__("Total")}}</label>



                        <span class="price">@{{total_price_html}}</span>



                    </li>



                    <li v-if="is_deposit_ready">



                        <label for="">{{__("Pay now")}}</label>



                        <span class="price">@{{pay_now_price_html}}</span>



                    </li>



                </ul>



                <div v-html="html"></div>



                <div class="submit-group">



                    <a class="btn btn-large" @click="doSubmit($event)" :class="{'disabled':onSubmit,'btn-success':(step == 2),'btn-primary':step == 1}" name="submit">



                        <span>{{__("BOOK NOW")}}</span>



                        <i v-show="onSubmit" class="fa fa-spinner fa-spin"></i>



                    </a>



                    <div class="alert-text mt10" v-show="message.content" v-html="message.content" :class="{'danger':!message.type,'success':message.type}"></div>



                </div>



            </div>



            <div class="form-send-enquiry" v-show="enquiry_type=='enquiry'">


                @php
                $user = auth()->user();
                @endphp
                <div class="enquiry-form">
                    <div class="enquiry-form-header">
                        <h5 class="enquiry-form-title">{{__("Plan your package!")}}</h5>
                        <h5 class="enquiry-form-description">{{__("For a truly unique experience, contact one of our travel advisors via WhatsApp or enter your details and we will contact you to help customise your package!")}}</h5>
                        <button type="button" class="btn btn-primary whatsapp-button-enquiry"><a href="https://wa.me/966920032086?text=Hi there! I’m interested in knowing more about the International Packages: Germany - SKU: PKG49461" target="_blank" rel="noreferrer"><span><svg focusable="false" color="inherit" fill="currentcolor" aria-hidden="true" role="presentation" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet" width="24px" height="24px" class="icon-whatsapp">
                                        <g>
                                            <path d="M95,49.247c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L5,95.406   l7.975-23.522c-4.023-6.606-6.34-14.354-6.34-22.637c0-24.213,19.781-43.841,44.184-43.841C75.223,5.406,95,25.034,95,49.247    M50.818,12.388c-20.484,0-37.146,16.535-37.146,36.859c0,8.066,2.629,15.535,7.076,21.611l-4.641,13.688l14.275-4.537   c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.858C87.964,28.924,71.301,12.388,50.818,12.388    M73.129,59.344c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.494c-0.993-0.359-1.717-0.539-2.438,0.536   c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.334   c-3.219-2.847-5.393-6.364-6.025-7.44c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882   c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.976   c-0.902-2.151-1.803-1.793-2.436-1.793c-0.631,0-1.354-0.09-2.076-0.09s-1.896,0.269-2.889,1.344   c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.114c0.541,0.716,7.49,11.92,18.5,16.223   C63.2,71.177,63.2,69.742,65.186,69.562c1.984-0.179,6.406-2.599,7.312-5.107C73.398,61.943,73.398,59.792,73.129,59.344"></path>
                                        </g>
                                    </svg>Contact us via WhatsApp</span></a></button>
                    </div>
                    <p class="or-spacing">{{ __("Or") }}</p>
                    <div class="enquiry-form-body">
                        <h5 class="enquiry-contact-form-title">{{__("Contact Details")}}</h5>
                        <input type="hidden" name="service_id" value="{{$row->id}}">
                        <input type="hidden" name="service_type" value="{{$service_type ?? 'tour'}}">
                        <div class="form-group">
                            <label for="enquiry_name">{{ __("Name *") }}</label>
                            <input type="text" class="form-control" id="enquiry_name" value="{{$user->display_name ?? ''}}" name="enquiry_name" placeholder="{{ __("Name *") }}">
                        </div>
                        <div class="form-group">
                            <label for="enquiry_email">{{ __("Email *") }}</label>
                            <input type="text" class="form-control" id="enquiry_email" value="{{$user->email ?? ''}}" name="enquiry_email" placeholder="{{ __("Email *") }}">
                        </div>
                        <div class="form-group">
                            <label for="enquiry_phone">{{ __("Phone") }}</label>
                            <input type="text" class="form-control" id="enquiry_phone" value="{{$user->phone ?? ''}}" name="enquiry_phone" placeholder="{{ __("Phone") }}">
                        </div>
                        <div class="form-group">
                            <label for="enquiry_note">{{ __("Note") }}</label>
                            <textarea class="form-control" id="enquiry_note" placeholder="{{ __("Note") }}" name="enquiry_note"></textarea>
                        </div>
                        @if(setting_item("booking_enquiry_enable_recaptcha"))
                        <div class="form-group">
                            {{recaptcha_field('enquiry_form')}}
                        </div>
                        @endif
                        <div class="message_box"></div>
                    </div>
                    <div class="enquiry-form-footer">
                        <button type="button" class="btn btn-primary btn-submit-new-enquiry">
                            {{__("Send now")}}
                            <i class="fa icon-loading fa-spinner fa-spin fa-fw" style="display: none"></i>
                        </button>
                    </div>
                </div>

                <!-- <button class="btn btn-primary" data-toggle="modal" data-target="#enquiry_form_modal">



                    {{ __("Contact Now") }}



                </button> -->



            </div>



        </div>



    </div>



</div>



@include("Booking::frontend.global.enquiry-form",['service_type'=>'tour'])
<style>
    .form-send-enquiry {
        text-align: start !important;
    }

    h5.enquiry-form-title {
        font-size: 24px;
        font-weight: bold;
        font-style: normal;
        line-height: 1.67;
        letter-spacing: normal;
        color: rgb(51, 51, 51);
    }

    h5.enquiry-form-description {
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        font-style: normal;
        line-height: 1.5;
        letter-spacing: normal;
        color: rgb(51, 51, 51);
        margin-bottom: 20px;
    }

    button.btn.btn-primary.whatsapp-button-enquiry {
        width: 100%;
        padding: 10px 0px 8px;
        font-size: 16px;
        font-weight: 600;
        margin-top: 10px;
        display: block;
        text-align: center;
        background-color: rgb(37, 211, 102) !important;
        border-radius: 50px !important;
        color: rgb(252, 252, 252) !important;
        line-height: 1.43;
        border: none;
    }

    button.btn.btn-primary.whatsapp-button-enquiry a {
        text-decoration: none;
        background-color: transparent;
        -webkit-text-decoration-skip: objects;
        color: rgb(252, 252, 252) !important;
    }

    .enquiry-form-header {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .enquiry-form-body {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    p.or-spacing {
        font-size: 14px;
        font-weight: 600;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: center;
        color: rgb(51, 51, 51);
        margin: 20px 0px;
        text-transform: uppercase;
    }

    h5.enquiry-contact-form-title {
        font-size: 16px;
        font-weight: 600;
        line-height: 1.2;
        color: rgb(58, 58, 58);
        text-transform: uppercase;
    }

    .enquiry-form {
        background: rgb(255, 255, 255);
        padding: 35px 20px 20px;
        margin-bottom: 20px;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    svg.icon-whatsapp {
        width: 30px;
        height: 30px;
        position: relative;
        top: -2px;
        margin-right: 10px;
    }

    button.btn.btn-primary.btn-submit-new-enquiry {
        cursor: pointer;
        font-style: normal;
        font-stretch: normal;
        font-weight: 600;
        line-height: 1.43;
        letter-spacing: normal;
        text-align: center;
        transition: 0.3s;
        width: 100%;
        padding: 15px 0px;
        font-size: 16px;
        border-radius: 50px;
        color: white;
        background: var(--primary-color);
        border: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.btn-submit-new-enquiry').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('.form-send-enquiry');
            console.log("sending enquiry");

            $.ajax({
                url: bookingCore.url + '/booking/addEnquiry',
                data: form.find('textarea,input,select').serialize(),
                dataType: 'json',
                type: 'post',
                beforeSend: function() {
                    form.find('.message_box').html('').hide();
                    form.find('.icon-loading').css("display", 'inline-block');
                },
                success: function(res) {
                    if (res.errors) {
                        res.message = '';
                        for (var k in res.errors) {
                            res.message += res.errors[k].join('<br>') + '<br>';
                        }
                    }
                    if (res.message) {
                        if (!res.status) {
                            form.find('.message_box').append('<div class="text text-danger">' + res.message + '</div>').show();
                        } else {
                            form.find('.message_box').append('<div class="text text-success">' + res.message + '</div>').show();
                        }
                    }

                    form.find('.icon-loading').hide();

                    if (res.status) {
                        form.find('textarea').val('');
                    }

                    if (typeof BravoReCaptcha != "undefined") {
                        BravoReCaptcha.reset('enquiry_form');
                    }
                },
                error: function(e) {
                    if (typeof BravoReCaptcha != "undefined") {
                        BravoReCaptcha.reset('enquiry_form');
                    }
                    form.find('.icon-loading').hide();
                }
            })
        })
    });
</script>