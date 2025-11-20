<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralRewardJob extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'run_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
