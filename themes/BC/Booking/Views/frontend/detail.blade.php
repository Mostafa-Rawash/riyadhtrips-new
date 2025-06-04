        @php
        $current = \App\Currency::getCurrent('currency_main');
            $actives = \App\Currency::getActiveCurrency();
        
        @endphp
        
        @extends('layouts.app')
        @push('css')
        <link href="{{ asset('module/booking/css/checkout.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
        @endpush
        @section('content')
        <div class="bravo-booking-page padding-content">
            <div class="container">
                @include ('Booking::frontend/global/booking-detail-notice')
                <div class="row booking-success-detail">
                    <div class="col-md-8">
                        @include ($service->booking_customer_info_file ?? 'Booking::frontend/booking/booking-customer-info')
                        <div class="text-center">
                            <a href="{{route('user.booking_history')}}" class="btn btn-primary">{{__('Booking History')}}</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        @include ($service->checkout_booking_detail_file ?? '')
                    </div>
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        $(document).ready(function() {
        
        
        
                            var bookingData = {};
        
                            // Loop through each li tag
                            $('.review-section:not(.total-review) li').each(function() {
                                var label = $(this).find('.label').text().trim();
                                var value = $(this).find('.val').text().trim();
        
                                // Only add to the JSON object if label and value exist
                                if (label && value) {
                                    bookingData[label] = value;
                                }
                            });
                            $('.review-section.total-review li div').each(function() {
                                var label = $(this).find('.label').text().replace(":", "").trim();
                                var value = $(this).find('.val').text().trim().replace(/[^0-9,.]/g, '').replace('.', '').replace(',', '.');
        
                                // Only add to the JSON object if label and value exist
                                if (label && value) {
                                    bookingData[label] = value;
                                }
                            });
        
        
                            // $('.review-section.total-review ul > li:nth-last-child(n+3)').each(function() {
                            //     var label = $(this).find('.label').text().trim();
                            //     var value = $(this).find('.val').text().trim().replace(/[^0-9,.]/g, '').replace('.', '').replace(',', '.');
        
                            //     // Only add to the JSON object if label and value exist
                            //     if (label && value) {
                            //         bookingData[label] = value;
                            //     }
                            // });
                            $('ul.booking-info-detail li').each(function() {
                                var key = $(this).find('span:first').text().replace(":", "").trim();
                                var value = $(this).contents().filter(function() {
                                    return this.nodeType === 3; // Text node
                                }).text().trim();
        
                                // Handle case where there's an inner span (like "Processing" status)
                                if (!value) {
                                    value = $(this).find('span').last().text().trim();
                                }
        
                                bookingData[key] = value;
                            });
        
                            let currancyField = $('.bravo-booking-page div.review-section.total-review li.final-total.d-block > div:nth-child(1) > div.val').text()
        
                            bookingData["Currency"] = "<?php echo strtoupper($current); ?>";
        
                            bookingData["Service name"] = $(".booking-review .booking-review-content .service-info .service-name a").text()
                            bookingData["Paid"] = parseFloat(bookingData["Paid"])
                            bookingData["Total"] = parseFloat(bookingData["Total"])
                            bookingData["Remain"] = bookingData["Remain"] ? parseFloat(bookingData["Remain"]) : 0
        
                            if (bookingData["Booking Status"] == "Paid") {
        
                                window.dataLayer = window.dataLayer || [];
                                window.dataLayer.push({
                                    event: 'Purchase', // Custom event name
                                    ...bookingData,
                                });
                            } else {
                                window.dataLayer = window.dataLayer || [];
                                window.dataLayer.push({
                                    event: 'UnsuccessfulPurchase', // Custom event name
                                    ...bookingData
                                });
                            }
        
                        });
                    </script>
        
                    <?php
        
        
                    // Get the current date 
                    $booking_date = (new DateTime($booking->created_at))->format('Y-m-d');
                    $comparisonDate = (new DateTime("2025-01-01"))->format('Y-m-d');
                    // Initialize totalPrice with the booking total
                    $totalPrice = $booking->total;
                    
                    // Check if the current currency is not SAR
                    // if ($current != "sar") {
                        foreach ($actives as $active) { // Iterate over the $actives array
                            if ($active['currency_main'] == "sar") { // Find the SAR currency
                                if ($active['rate'] != 0) { // Prevent division by zero
                                    $totalPrice = $booking->total / $active['rate']; // Convert to SAR
                                } else {
                                    // Handle the case where rate is zero
                                    $totalPrice = $totalPrice; // Or some other fallback value
                                }
                                break; // Exit the loop once SAR is found
                            }
                        }
                    // }
                    if ($booking->status == "paid" &&  $booking->gateway == "clickpay" && $booking_date >= $comparisonDate) {
                        // if ($booking->status == "paid" &&  $booking->gateway == "clickpay") {
                        //    Tour description
                        //    Tour description
                        $description = "";
                        $person_types = $booking->getJsonMeta('person_types');
                        if (!empty($person_types)) {
        
                            foreach ($person_types as $type) {
                                $description .= $type['number'] . " " . __($type['name']) .   "\n";
                            }
                        } else
                            $description .=   "\n" . $booking->total_guests . " " . __("Guests");
        
                        // JSON data
                        $data = [
                            "invoice" => [
                                "contact_id" => 2,
                                "reference" => "SITE0" . $booking->id,
                                "payment_method" => $booking->gateway,
                                "status" => "Paid",
                                "total" => $totalPrice,
                                "created_at" => $booking_date,
                                "issue_date" => $booking_date,
                                "due_date" => $booking_date,
                                "inventory_id" => 1,
                                "line_items" => [
                                    [
                                        "product_id" => 1,
                                        "description" => $description,
                                        "product_name" => $booking->service->title,
                                        "quantity" =>"1.0",
                                        "unit_price" => $totalPrice,
                                        "unit_type" => 7,
                                        "discount_amount" => $booking->coupon_amount,
                                        "tax_percent" => "15.0",
                                        "line_total" => $totalPrice,
                                        "is_inclusive" => true,
                                    ]
                                ],
                                "payments" => []
                            ]
                        ];
                        $apiKey = config('services.api_key');
                        $headers = [
                            'API-KEY' => $apiKey,
                            'Content-Type' => 'application/json'
                        ];
        
                        try {
                            $apiResponse = [
                                'invoice' => null,
                                'payment' => null,
                                'errors' => []
                            ];
        
        
                            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->post('https://www.qoyod.com/api/2.0/invoices', $data);
                            $invoiceResponse = $response->json();
                            if (($response->successful() || $response->status() == 422) && isset($invoiceResponse['invoice']['id'])) {
                                $apiResponse['invoice'] = $invoiceResponse; // Store invoice response
                                $invoiceId = $invoiceResponse['invoice']['id']; // Extract the invoice ID
        
                                // Step 2: Record Payment
                                $paymentData = [
                                    "invoice_payment" => [
                                        "reference" => "PAYMENT" . $booking->id, // Unique payment reference
                                        "invoice_id" => $invoiceId,              // The ID of the created invoice
                                        "account_id" => 8,             // Account credited for payment
                                        "date" => $booking_date,                 // Payment date
                                        "amount" => $totalPrice,             // Payment amount
                                    ]
                                ];
        
                                $paymentResponse = \Illuminate\Support\Facades\Http::withHeaders($headers)->post('https://www.qoyod.com/api/2.0/invoice_payments', $paymentData);
                                $paymentApiResponse = $paymentResponse->json();
        
                                if ($paymentResponse->successful()) {
                                    $apiResponse['payment'] = $paymentApiResponse; // Store payment response
                                } else {
                                    $apiResponse['errors'][] = [
                                        'payment_error' => $paymentApiResponse
                                    ]; // Store payment errors
                                }
                            } else {
                                $apiResponse['errors'][] = [
                                    'invoice_error' => $invoiceResponse
                                ]; // Store invoice errors
                            }
                        } catch (\Exception $e) {
                            $apiResponse = ['error' => $e->getMessage()];
                        }
                    }
                    ?>
                    <script>
                        // console.log("api request")
                        // console.log("<?php  // echo json_encode($apiResponse) ?>");
                    </script>
                </div>
            </div>
        </div>
        @endsection