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

    protected $appends = [
        'preview_url',
    ];

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }

    /**
     * Get the route URL to stream preview image directly from Laravel,
     * avoiding issues with broken or restricted server symlinks on live hosting.
     */
    public function getPreviewUrlAttribute()
    {
        if (!$this->background_image) {
            return null;
        }
        return route('admin.certificates.templates.preview', $this->id);
    }

    /**
     * Backward-compatible alias for preview URL.
     */
    public function getBackgroundImageUrlAttribute()
    {
        return $this->preview_url;
    }
}

