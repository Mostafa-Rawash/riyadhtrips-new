<?php

namespace Modules\Visa\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Modules\Core\Models\SEO;

class Visa extends BaseModel
{
    use SoftDeletes;

    protected $table = 'visa_submissions';
    
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
        'created_at' => 'date',
        'updated_at' => 'date',
        'scheduled_trip_date' => 'date',
        'total_price' => 'decimal:2'
    ];

    public static function getModelName()
    {
        return __("Visa");
    }

    public static function search(Request $request)
    {
        $query = parent::query();
        
        if (!empty($request->query('s'))) {
            $s = $request->query('s');
            $query->where(function ($query) use ($s) {
                $query->where('first_name', 'LIKE', '%' . $s . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $s . '%')
                    ->orWhere('email', 'LIKE', '%' . $s . '%')
                    ->orWhere('unique_code', 'LIKE', '%' . $s . '%');
            });
        }

        if (!empty($request->query('country'))) {
            $country = $request->query('country');
            $query->where('country_name', $country);
        }

        if (!empty($request->query('status'))) {
            $status = $request->query('status');
            $query->where('status', $status);
        }

        if (!empty($request->query('payment_status'))) {
            $payment_status = $request->query('payment_status');
            $query->where('payment_status', $payment_status);
        }

        $query->orderBy('id', 'desc');

        return $query;
    }

    public function getStatusNameAttribute()
    {
        $statuses = [
            0 => 'Pending',
            1 => 'Processing',
            2 => 'Approved',
            3 => 'Rejected',
            4 => 'Cancelled'
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusClassAttribute()
    {
        $classes = [
            0 => 'warning',
            1 => 'primary',
            2 => 'success',
            3 => 'danger',
            4 => 'secondary'
        ];

        return $classes[$this->status] ?? 'info';
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}