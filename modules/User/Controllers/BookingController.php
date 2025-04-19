<?php

namespace Modules\User\Controllers;



use Modules\FrontendController;

use Illuminate\Support\Facades\Auth;

use Validator;

use Modules\Booking\Models\Booking;
use PDF;

class BookingController extends FrontendController

{

    private Booking $booking;



    public function __construct(Booking $booking)

    {

        parent::__construct();

        $this->booking = $booking;

    }



    public function bookingInvoice($code)

    {

        $booking = $this->booking->where('code', $code)->first();

        $user_id = Auth::id();

        if (empty($booking)) {

            return redirect('user/booking-history');

        }

        if ($booking->customer_id != $user_id and $booking->vendor_id != $user_id) {

            return redirect('user/booking-history');

        }

        $data = [

            'booking'    => $booking,

            'service'    => $booking->service,

            'page_title' => __("Invoice")

        ];

        return view('User::frontend.bookingInvoice', $data);

    }

    public function downloadInvoice($code)
    {
        $booking = $this->booking->where('code', $code)->first();
        $user_id = Auth::id();
    
        // Check if the booking exists and the user has access
        if (empty($booking) || ($booking->customer_id != $user_id && $booking->vendor_id != $user_id)) {
            abort(403, 'Unauthorized access to this booking.');
        }
    
        // Get the service details
        $service = $booking->service;
    
        // Prepare the data to be passed to the view
        $data = [
            'booking'    => $booking,
            'service'    => $service,
            'page_title' => __("Invoice")
        ];
    
        // Generate the PDF content using a Blade view and pass the same data
        $pdf = PDF::loadView('User::frontend.bookingDownloads', $data);
    
        // Return the PDF download
        return $pdf->download('invoice-' . $code . '.pdf');
    }
    



}

