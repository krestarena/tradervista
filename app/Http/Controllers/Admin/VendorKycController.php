<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorKycSubmission;
use App\Notifications\ShopVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class VendorKycController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $submissions = VendorKycSubmission::with(['user.shop'])->latest();

        if ($status) {
            $submissions->where('status', $status);
        }

        if ($search) {
            $submissions->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $submissions = $submissions->paginate(20)->appends($request->only(['status', 'search']));

        return view('backend.tradevista.vendor_kyc.index', compact('submissions', 'status', 'search'));
    }

    public function show(VendorKycSubmission $submission)
    {
        $submission->load('user.shop');
        return view('backend.tradevista.vendor_kyc.show', compact('submission'));
    }

    public function approve(Request $request, VendorKycSubmission $submission)
    {
        $submission->update([
            'status' => 'approved',
            'rejection_notes' => null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->markShopVerification($submission, true);
        $this->notifyVendor($submission, 'approved');

        flash(translate('KYC approved and vendor badge updated.'))->success();
        return redirect()->route('admin.vendor-kyc.show', $submission->id);
    }

    public function reject(Request $request, VendorKycSubmission $submission)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $submission->update([
            'status' => 'rejected',
            'rejection_notes' => ['reason' => $validated['reason']],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->markShopVerification($submission, false);
        $this->notifyVendor($submission, 'rejected');

        flash(translate('KYC rejected and vendor notified.'))->success();
        return redirect()->route('admin.vendor-kyc.show', $submission->id);
    }

    protected function markShopVerification(VendorKycSubmission $submission, bool $approved): void
    {
        $shop = optional($submission->user)->shop;
        if (!$shop) {
            return;
        }

        $shop->verification_status = $approved ? 1 : 0;
        if ($approved) {
            $shop->verification_info = json_encode($submission->documents);
        }
        $shop->save();
        Cache::forget('verified_sellers_id');
    }

    protected function notifyVendor(VendorKycSubmission $submission, string $status): void
    {
        $user = $submission->user;
        if (!$user) {
            return;
        }

        $notificationType = $status === 'approved'
            ? 'shop_verify_request_approved'
            : 'shop_verify_request_rejected';

        $data = [
            'shop' => optional($user)->shop,
            'status' => $status,
            'notification_type_id' => get_notification_type($notificationType, 'type')->id,
        ];

        Notification::send(collect([$user]), new ShopVerificationNotification($data));
    }
}
