<?php

namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaApplication extends Model
{
    protected $connection = 'visa_external';
    protected $table = 'visa_application_summary';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    // Reference to the unique_code that can link to submissions table
    protected $appends = ['submission_details'];

    protected $fillable = [
        'unique_code',
        'first_name',
        'last_name',
        'user_id',
        'email',
        'phone',
        'contact_type',
        'total_price',
        'scheduled_trip_date',
        'country_name',
        'visa_name',
        'embassy_name',
        'adults',
        'childrens',
        'relationship',
        'payment_status',
        'payment_method',
        'appointment',
        'status'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'scheduled_trip_date' => 'date',
        'adults' => 'integer',
        'childrens' => 'integer',
        'status' => 'integer'
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

    // Relationship with the main user model
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    // Status helper methods
    public function getStatusNameAttribute()
    {
        $statuses = [
            0 => 'Pending',
            1 => 'Processing',
            2 => 'Approved',
            3 => 'Rejected',
            4 => 'Cancelled',
            5 => 'Completed'
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusClassAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'warning';
            case 1:
                return 'info';
            case 2:
                return 'success';
            case 3:
                return 'danger';
            case 4:
                return 'dark';
            case 5:
                return 'success';
            default:
                return 'secondary';
        }
    }

    // Payment status helper methods
    public function getPaymentStatusClassAttribute()
    {
        switch (strtolower($this->payment_status)) {
            case 'paid':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    // Scope for user's visa applications
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering by payment status
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    // Get total number of travelers
    public function getTotalTravelersAttribute()
    {
        return ($this->adults ?? 0) + ($this->childrens ?? 0);
    }

    // Format price
    public function getFormattedPriceAttribute()
    {
        return number_format($this->total_price, 2, '.', ',') . ' SR';
    }

    // Check if application can be edited
    public function canEdit()
    {
        return in_array($this->status, [0, 1]); // Pending or Processing
    }

    // Check if application can be cancelled
    public function canCancel()
    {
        return in_array($this->status, [0, 1]); // Pending or Processing
    }
    
    // Get detailed submission data
    public function getSubmissionDetailsAttribute()
    {
        return DB::connection($this->connection)
            ->table('submissions')
            ->where('unique_code', $this->unique_code)
            ->first();
    }
    
    // Define a relationship with multiple submissions in the submissions table
    public function submissions()
    {
        return $this->hasMany(VisaSubmission::class, 'unique_code', 'unique_code');
    }

    // Get visa applications summary for dashboard
    public static function getCustomerSummary($userId)
    {
        $applications = self::forUser($userId)->get();
        
        return [
            'total' => $applications->count(),
            'pending' => $applications->where('status', 0)->count(),
            'processing' => $applications->where('status', 1)->count(),
            'approved' => $applications->where('status', 2)->count(),
            'rejected' => $applications->where('status', 3)->count(),
            'completed' => $applications->where('status', 5)->count(),
            'total_spent' => $applications->where('payment_status', 'paid')->sum('total_price')
        ];
    }
}