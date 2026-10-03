<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    protected $table = 'tbl_certificate_templates';

    protected $fillable = [
        'name',
        'description',
        'certificate_type',
        'background_image',
    ];

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }
}

