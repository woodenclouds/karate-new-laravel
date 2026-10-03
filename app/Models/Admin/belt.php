<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Belt extends Model
{
    use HasFactory;

    protected $table = 'tbl_belt';

    protected $fillable = ['from_belt', 'to_belt', 'fees'];
}