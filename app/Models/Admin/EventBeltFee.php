<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventBeltFee extends Model
{
    use HasFactory;

    protected $table = 'event_belt_fees';

    protected $fillable = [
        'event_id',
        'belt_id',
        'fee',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function belt()
    {
        return $this->belongsTo(\App\Models\Admin\Belt::class, 'belt_id');
    }
}
