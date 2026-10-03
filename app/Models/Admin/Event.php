<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'event';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'event_date',
        'event_time',
        'venue',
        'fee',
        'additional_fee',
        'is_active',
    ];
    

    public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}


    public function registrations()
    {
        return $this->hasMany(\App\Models\Registration::class, 'event_id');
    }

    public function eventBeltFees()
    {
        return $this->hasMany(EventBeltFee::class, 'event_id');
    }

  
}
