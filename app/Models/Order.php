<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Order extends Model
{
    use PreventDemoModeChanges;

    public const DISPATCH_PLATFORM = 'platform_dispatch';
    public const DISPATCH_OWN = 'own_dispatch';
    public const PAYMENT_PROTECTION_ACTIVE = 'active';
    public const PAYMENT_PROTECTION_RELEASED = 'released';
    public const PAYMENT_PROTECTION_NOT_APPLICABLE = 'not_applicable';
    public const PAYMENT_PROTECTION_PAUSED = 'paused';

    protected $casts = [
        'payment_protection_released_at' => 'datetime',
        'payment_protection_hold_expires_at' => 'datetime',
        'pickup_ready_at' => 'datetime',
        'pickup_window_end' => 'datetime',
    ];
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }

    public function commissionHistory()
    {
        return $this->hasOne(CommissionHistory::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function isPaymentProtectionActive(): bool
    {
        return $this->payment_protection_status === self::PAYMENT_PROTECTION_ACTIVE;
    }
}
