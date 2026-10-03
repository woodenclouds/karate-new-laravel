<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = 'tbl_form';

    protected $fillable = [
        'category_id', 'title'
    ];

   public function category()
{
    return $this->belongsTo(\App\Models\Admin\Category::class);
}

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function formFields()
{
    return $this->hasMany(FormField::class, 'form_id');
}
}
