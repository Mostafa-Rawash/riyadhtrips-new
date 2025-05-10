<div class="bravo_filter">
    <form action="{{ route("visa.search") }}" class="bravo_form_filter">
        @if(request()->input('s'))
            <input type="hidden" name="s" value="{{ request()->input('s') }}">
        @endif
        <div class="filter-title">
            {{__("FILTER BY")}}
        </div>
        <div class="g-filter-item">
            <div class="item-title">
                <h3>{{ __("Status") }}</h3>
                <i class="fa fa-angle-up" data-toggle="collapse" data-target="#statusCollapse"></i>
            </div>
            <div id="statusCollapse" class="collapse show">
                <div class="item-content">
                    <div class="btn-group">
                        <label class="btn-filter btn @if(request()->query('status') == '') active @endif">
                            <input type="radio" name="status" value="" @if(request()->query('status') == '') checked @endif>
                            {{__("All Status")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('status') == '0') active @endif">
                            <input type="radio" name="status" value="0" @if(request()->query('status') == '0') checked @endif>
                            {{__("Pending")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('status') == '1') active @endif">
                            <input type="radio" name="status" value="1" @if(request()->query('status') == '1') checked @endif>
                            {{__("Processing")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('status') == '2') active @endif">
                            <input type="radio" name="status" value="2" @if(request()->query('status') == '2') checked @endif>
                            {{__("Approved")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('status') == '3') active @endif">
                            <input type="radio" name="status" value="3" @if(request()->query('status') == '3') checked @endif>
                            {{__("Rejected")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('status') == '4') active @endif">
                            <input type="radio" name="status" value="4" @if(request()->query('status') == '4') checked @endif>
                            {{__("Cancelled")}}
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="g-filter-item">
            <div class="item-title">
                <h3>{{ __("Payment Status") }}</h3>
                <i class="fa fa-angle-up" data-toggle="collapse" data-target="#paymentStatusCollapse"></i>
            </div>
            <div id="paymentStatusCollapse" class="collapse show">
                <div class="item-content">
                    <div class="btn-group">
                        <label class="btn-filter btn @if(request()->query('payment_status') == '') active @endif">
                            <input type="radio" name="payment_status" value="" @if(request()->query('payment_status') == '') checked @endif>
                            {{__("All Payment Status")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('payment_status') == 'pending') active @endif">
                            <input type="radio" name="payment_status" value="pending" @if(request()->query('payment_status') == 'pending') checked @endif>
                            {{__("Pending")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('payment_status') == 'paid') active @endif">
                            <input type="radio" name="payment_status" value="paid" @if(request()->query('payment_status') == 'paid') checked @endif>
                            {{__("Paid")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('payment_status') == 'partial') active @endif">
                            <input type="radio" name="payment_status" value="partial" @if(request()->query('payment_status') == 'partial') checked @endif>
                            {{__("Partially Paid")}}
                        </label>
                        <label class="btn-filter btn @if(request()->query('payment_status') == 'cancelled') active @endif">
                            <input type="radio" name="payment_status" value="cancelled" @if(request()->query('payment_status') == 'cancelled') checked @endif>
                            {{__("Cancelled")}}
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="g-filter-item">
            <div class="item-title">
                <h3>{{ __("Date Range") }}</h3>
                <i class="fa fa-angle-up" data-toggle="collapse" data-target="#dateRangeCollapse"></i>
            </div>
            <div id="dateRangeCollapse" class="collapse show">
                <div class="item-content">
                    <div class="form-group">
                        <label>{{__("From Date")}}</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker" name="from_date" value="{{ request()->input('from_date') }}" placeholder="{{__("From Date")}}">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{__("To Date")}}</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker" name="to_date" value="{{ request()->input('to_date') }}" placeholder="{{__("To Date")}}">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="g-filter-item">
            <div class="item-title">
                <h3>{{ __("Trip Date") }}</h3>
                <i class="fa fa-angle-up" data-toggle="collapse" data-target="#tripDateCollapse"></i>
            </div>
            <div id="tripDateCollapse" class="collapse show">
                <div class="item-content">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control datepicker" name="trip_date" value="{{ request()->input('trip_date') }}" placeholder="{{__("Select Trip Date")}}">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="g-filter-item">
            <div class="item-content">
                <div class="btn-more-item">
                    <button type="submit" class="btn btn-primary">{{__("Apply Filters")}}</button>
                </div>
            </div>
        </div>
    </form>
</div>