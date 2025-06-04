<?php

namespace Modules\Tour\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tour\Controllers\TimeSlotAvailabilityController;

/**
 * API Tour Controller - Handles time slot related API requests
 */
class TourController extends Controller
{
    protected $timeSlotController;

    public function __construct()
    {
        $this->timeSlotController = new TimeSlotAvailabilityController();
    }

    /**
     * Method that the route is expecting (availableTimeSlots)
     */
    public function availableTimeSlots(Request $request)
    {
        return $this->timeSlotController->getAvailableSlots($request);
    }

    /**
     * Alternative method name (getAvailableSlots)
     */
    public function getAvailableSlots(Request $request)
    {
        return $this->timeSlotController->getAvailableSlots($request);
    }

    /**
     * Check slot capacity
     */
    public function checkSlotCapacity(Request $request)
    {
        return $this->timeSlotController->checkSlotCapacity($request);
    }

    /**
     * Get real-time updates
     */
    public function getRealTimeUpdates(Request $request)
    {
        return $this->timeSlotController->getRealTimeUpdates($request);
    }
}
