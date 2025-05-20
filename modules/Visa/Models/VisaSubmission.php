<?php

namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaSubmission extends Model
{
    protected $connection = 'visa_external';
    protected $table = 'submissions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'unique_code',
        'first_name',
        'last_name',
        'country_id',
        'country_name',
        'visa_id',
        'visa_name',
        'embassy_name',
        'scheduled_trip_date',
        'passport_url',
        'payment_status',
        'payment_method',
        'adults',
        'childrens',
        'relationship',
        'visa_price',
        'preferred_choice',
        'family_card',
        'passport',
        'transactionid',
        'last_visa',
        'last_visa_uploads',
        'marital_status',
        'marital_status_uploads',
        'paid_by',
        'paid_by_relation',
        'account_states',
        'account_states_uploads',
        'salary_id',
        'salary_id_uploads',
        'civil_url',
        'schengen_visa',
        'account_owner',
        'traveler_relation',
        'relation_passport',
        'mother_name',
        'mother_last',
        'nationality',
        'persnol_img',
        'us_visa_upload',
        'usvisa_before',
        'usvisa_cancel',
        'us_relatives',
        'Visacancel_explain',
        'last_country',
        'travel_year',
        'appointment',
        'stay_length',
        'arrival_date'
    ];
    
    // Constructor with fallback connection logic
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        try {
            // Test the primary connection
            DB::connection('visa_external')->getPdo();
        } catch (\Exception $e) {
            // If primary connection fails, log the error and try the fallback
            Log::error("Failed to connect to visa_external database: " . $e->getMessage());
            
            try {
                // Test the fallback connection
                DB::connection('visa_fallback')->getPdo();
                
                // If fallback works, use it
                $this->connection = 'visa_fallback';
                Log::info("Using visa_fallback connection as fallback");
            } catch (\Exception $e2) {
                // Both connections failed
                Log::error("Failed to connect to visa_fallback database: " . $e2->getMessage());
            }
        }
    }

    // Define the relationship with the summary
    public function summary()
    {
        return $this->belongsTo(VisaApplication::class, 'unique_code', 'unique_code');
    }

    // Helper method to get passport URL with proper domain if needed
    public function getFullPassportUrlAttribute()
    {
        if (empty($this->passport_url)) {
            return null;
        }
        
        // If the URL is already absolute, return it
        if (filter_var($this->passport_url, FILTER_VALIDATE_URL)) {
            return $this->passport_url;
        }
        
        // Otherwise append domain (adjust this based on your setup)
        return config('app.visa_document_url', 'https://example.com/documents/') . $this->passport_url;
    }

    // Helper method to get personal image URL with proper domain if needed
    public function getFullPersonalImageUrlAttribute()
    {
        if (empty($this->persnol_img)) {
            return null;
        }
        
        // If the URL is already absolute, return it
        if (filter_var($this->persnol_img, FILTER_VALIDATE_URL)) {
            return $this->persnol_img;
        }
        
        // Otherwise append domain (adjust this based on your setup)
        return config('app.visa_document_url', 'https://example.com/documents/') . $this->persnol_img;
    }

    // Format trip date 
    public function getFormattedTripDateAttribute()
    {
        if (empty($this->scheduled_trip_date)) {
            return 'Not specified';
        }
        
        try {
            $date = new \DateTime($this->scheduled_trip_date);
            return $date->format('M d, Y');
        } catch (\Exception $e) {
            return $this->scheduled_trip_date;
        }
    }

    // Format visa price
    public function getFormattedVisaPriceAttribute()
    {
        if (!is_numeric($this->visa_price)) {
            return $this->visa_price;
        }
        
        return number_format($this->visa_price, 2, '.', ',') . ' SR';
    }
}