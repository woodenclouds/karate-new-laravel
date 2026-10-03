<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'tbl_contact'; // must match your DB table
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message'];
}
