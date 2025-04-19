@extends('Layout::empty')

@push('css')
<style type="text/css">
    html,
    body {
        background: #f0f0f0;
        color: #1a2b48;
        font-family: Poppins, sans-serif;
    }

    .bravo_topbar,
    .bravo_header,
    .bravo_footer {
        display: none;
    }

    .invoice-amount {
        margin-top: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px 20px;
        display: inline-block;
        text-align: center;
    }

    .email_new_booking .b-table {
        width: 100%;
    }

    .email_new_booking .val {
        text-align: right;
    }

    .email_new_booking td,
    .email_new_booking th {
        padding: 5px;
    }

    .email_new_booking .val table {
        text-align: left;
    }

    .email_new_booking .b-panel-title,
    .email_new_booking .booking-number,
    .email_new_booking .booking-status,
    .email_new_booking .manage-booking-btn {
        display: none;
    }

    .email_new_booking .fsz21 {
        font-size: 18px;
    }

    .table-service-head {
        border: 1px solid #ddd;
        background-color: #f9f9f9;
    }

    .table-service-head th {
        padding: 5px 15px;
    }

    #invoice-print-zone {
        background: white;
        padding: 15px;
        margin: 90px auto 40px auto;
        max-width: 1025px;
    }

    .invoice-company-info {
        margin-top: 15px;
    }

    .invoice-company-info p {
        margin-bottom: 2px;
        font-weight: normal;
    }

    /* 28-12-2024 */
    .hed {
        background: #33b28c;
        padding: 6px;
        color: #fff;
    }

    .bg_bx td {
        background: #2fb38d24;
    }

    .tour_name a {
        color: #2fb38d;
    }

    .in_disc{
        padding: 0;
        text-align: left;
        list-style: none;
        margin-top: 0;
        margin-bottom: 10px;
    }

    .in_disc li{
        margin-bottom: 3px;
    }

    .invice_disc h4{
        margin-bottom: 0.6rem;
    }

    .invice_disc h4{
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 0;
    }

    .invice_disc p, .invice_disc span strong{
        font-size: 14px;
        font-weight: 400;
        text-align: left;
    }

    .image_bx{
        column-count: 4; /* Define the number of columns by default */
        column-gap: 16px; /* Space between columns */
    }

    img{
        width: 100%;
    }

    /* .bord_top{
            border-top: 1px solid #33b28c;
        } */
</style>
<link href="{{ asset('module/user/css/user.css') }}" rel="stylesheet">
<script>
    window.print();
</script>
<div id="invoice-print-zone">
    <table width="100%" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="50%" align="left" class="text-left">
                     @if( !empty($logo = setting_item('logo_invoice_id') ?? setting_item('logo_id') ))
                        <img style="max-width: 200px;" src="{{public_path( 'uploads/0000/7/2024/10/10/riyadh-trips-logo-dark.png')}}" alt="{{setting_item("site_title")}}">
                    @endif
                    {{-- <img style="max-width: 200px;"
                        src="https://riyadhtrips.com/uploads/0000/7/2024/10/10/riyadh-trips-logo-dark.png"
                        alt="riyadh trips"> --}}
                    <div class="invoice-company-info">
                        {!! setting_item_with_lang("invoice_company_info") !!}
                    </div>
                </th>
                <th width="50%" align="right" class="text-right">
                   {{--  <h2 class="invoice-text-title">{{__("INVOICE")}}</h2>
                    {{__('Invoice #: :number',['number'=>$booking->id])}}
                    <br>
                    {{__('Created: :date',['date'=>display_date($booking->created_at)])}} --}}
                </th>
            </tr>
            <tr>
                <th width="50%">
                    {!! nl2br(setting_item('invoice_company')) !!}
                </th>
                <th width="50%" align="right" class="text-right">
                    {{-- <div class="invoice-amount">
                        <div class="label">{{__("Amount due:")}}</div>
                        <div class="amount" style="font-size: 24px;"><strong>{{format_money($booking->total -
                                $booking->paid)}}</strong>
                        </div>
                    </div> --}}
                </th>
            </tr>
        </thead>
    </table>
    <br>
   {{--  <div class="customer-info">
        <h5 class="hed"><strong>{{__('Billing to:')}}</strong></h5>
        <span class="name_txt" style="text-transform: capitalize;">{{$booking->first_name.' '.$booking->last_name}}</span> --}}
        {{-- <span class="name_txt" style="text-transform: capitalize;"><b>John Doe</b></span> --}}
       {{--  <br>
        <br>
        <span>{{$booking->email}}</span><br>
        <span>{{$booking->phone}}</span><br>
         <span>{{$booking->address}}</span><br>  --}}
        {{-- <span>819 N 13th St</span><br> --}}
        {{-- <span>{{implode(', ',[$booking->city,$booking->state,$booking->zip_code,get_country_name($booking->country)])}}</span><br> --}}
        {{-- <span>Harlingen, Texas, United States, 78550</span> --}}
    {{-- </div> --}}
    {{-- <br> --}}
    
    @if(!empty($service->email_new_booking_file_downloads))
    <div class="email_new_booking">
        @include($service->email_new_booking_file_downloads ?? '')
    </div>
    @endif
</div>
@endpush
@push('js')
<script type="text/javascript" src="{{ asset(" module/user/js/user.js") }}"></script>
@endpush