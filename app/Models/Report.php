<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public const REASONS = [
        'spam'           => 'Spam or misleading',
        'prohibited'     => 'Prohibited item',
        'scam'           => 'Suspected scam',
        'wrong_category' => 'Wrong category',
        'duplicate'      => 'Duplicate ad',
        'other'          => 'Other',
    ];

    public const STATUS_OPEN      = 'open';
    public const STATUS_RESOLVED  = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [

        'reason',
        'details'
    ];

    protected $casts = [
        'handled_at' => 'datetime'
    ];

    public function ad()
    {

        return $this->belongsTo(Ad::class)->withTrashed();

    }
    public function reporter()
    {

        return $this->belongsTo(User::class, 'user_id');

    }
    public function handler()
    {

        return $this->belongsTo(User::class, 'handled_by');

    }
}
