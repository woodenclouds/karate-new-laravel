<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $table = 'tbl_form_fields';

    protected $fillable = [
        'form_id', 'label', 'type', 'required', 'options', 'order'
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    
    public function form()
{
    return $this->belongsTo(Form::class, 'form_id');
}

}
