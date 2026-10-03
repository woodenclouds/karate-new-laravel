<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Event; // ✅ Add this
use App\Models\Admin\belt;

class Registration extends Model
{
    protected $table = 'tbl_registration';

    protected $fillable = [
        'event_id',
        'submitted_data',
        'amount',
        'entered_by',
        'registration_code',
        'payment_id',
        'razorpay_order_id',
        'status',
        'is_attended',
        'rank',
    ];

    protected $casts = [
        'submitted_data' => 'array',
        'is_attended' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id'); // ✅ Now it uses the correct model
    }
    public function belt()
    {
        return $this->belongsTo(Belt::class, 'id');
    }
}
