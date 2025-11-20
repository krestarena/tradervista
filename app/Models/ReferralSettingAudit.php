<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSettingAudit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
