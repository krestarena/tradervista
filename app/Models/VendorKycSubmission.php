<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorKycSubmission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'documents' => 'array',
        'rejection_notes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
