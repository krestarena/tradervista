<?php

namespace App\Models;

use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    use PreventDemoModeChanges;

    public const KYC_STATUS_DRAFT = 'draft';
    public const KYC_STATUS_PENDING = 'pending';
    public const KYC_STATUS_APPROVED = 'approved';
    public const KYC_STATUS_REJECTED = 'rejected';

    protected $casts = [
        'service_area_cities' => 'array',
        'kyc_submitted_at' => 'datetime',
        'kyc_reviewed_at' => 'datetime',
        'tradevista_approved_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'kyc_document_type',
        'kyc_document_number',
        'kyc_document_upload_id',
        'license_number',
        'service_area_cities',
        'default_rate',
        'default_eta_hours',
        'kyc_submitted_at',
        'kyc_reviewed_at',
        'tradevista_approved_at',
        'kyc_status',
        'kyc_rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $builder): Builder
    {
        return $builder->where('kyc_status', self::KYC_STATUS_APPROVED);
    }

    public function isApproved(): bool
    {
        return $this->kyc_status === self::KYC_STATUS_APPROVED;
    }

    public function serviceAreaCityNames(): array
    {
        $cityIds = array_filter((array) $this->service_area_cities);

        if (empty($cityIds)) {
            return [];
        }

        return City::whereIn('id', $cityIds)->pluck('name')->toArray();
    }
}
