<?php

namespace App\Services\TradeVista;

use App\Models\Product;
use App\Models\User;
use App\Models\VendorKycSubmission;
class VendorVerificationService
{
    public function canPublishProducts(User $user): bool
    {
        return optional($user->shop)->verification_status === 1;
    }

    public function ensureProductDraftForUnverified(User $user, Product $product): bool
    {
        if ($this->canPublishProducts($user)) {
            return true;
        }

        if ($product->published != 0) {
            $product->published = 0;
            $product->save();
        }

        return false;
    }

    public function latestSubmissionFor(User $user): ?VendorKycSubmission
    {
        return VendorKycSubmission::where('user_id', $user->id)
            ->latest()
            ->first();
    }
}
