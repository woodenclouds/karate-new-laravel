<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Event;
use App\Models\CertificateTemplate;

class Certificate extends Model
{
    protected $table = 'tbl_certificate';

    protected $fillable = [
        'event_id',
        'event_title',
        'event_date',
        'venue',
        'certificate_type',
        'template_id',
        'excel_file_path',
        'participants_data',
        'certificate_count',
        'logo',
    ];

    protected $casts = [
        'event_date' => 'date',
        'participants_data' => 'array',
        'certificate_count' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Venue printed on the certificate: the linked event's venue, otherwise the stored copy.
     */
    public function certificateVenue(): string
    {
        if ($this->event_id) {
            $fromEvent = trim((string) ($this->event->venue ?? ''));
            if ($fromEvent !== '') {
                return $fromEvent;
            }
        }

        return trim((string) ($this->venue ?? ''));
    }
}

