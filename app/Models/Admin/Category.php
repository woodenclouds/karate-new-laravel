<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'tbl_category';

    protected $fillable = ['name'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

        public function form()
        {
            return $this->hasOne(Form::class, 'category_id');
        }
}