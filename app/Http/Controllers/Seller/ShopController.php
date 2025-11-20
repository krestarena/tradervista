<?php

namespace App\Http\Controllers\Seller;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use App\Models\VendorKycSubmission;
use App\Notifications\ShopVerificationNotification;
use Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;
        return view('seller.shop', compact('shop'));
    }

    public function update(Request $request)
    {
        $shop = Shop::find($request->shop_id);

        if ($request->has('name') && $request->has('address')) {
            if ($request->has('shipping_cost')) {
                $shop->shipping_cost = $request->shipping_cost;
            }

            $shop->name             = $request->name;
            $shop->address          = $request->address;
            $shop->phone            = $request->phone;
            $shop->slug             = preg_replace('/\s+/', '-', $request->name) . '-' . $shop->id;
            $shop->meta_title       = $request->meta_title;
            $shop->meta_description = $request->meta_description;
            $shop->logo             = $request->logo;
        }

        if ($request->has('delivery_pickup_longitude') && $request->has('delivery_pickup_latitude'))
        {
            $shop->delivery_pickup_longitude    = $request->delivery_pickup_longitude;
            $shop->delivery_pickup_latitude     = $request->delivery_pickup_latitude;
        } 
        elseif ($request->has('facebook') || $request->has('google') || $request->has('twitter') ||$request->has('youtube') || $request->has('instagram'))
        {
            $shop->facebook = $request->facebook;
            $shop->instagram = $request->instagram;
            $shop->google = $request->google;
            $shop->twitter = $request->twitter;
            $shop->youtube = $request->youtube;
        }

        if ($shop->save()) {
            flash(translate('Your Shop has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function bannerUpdate(Request $request){
        $shop = Shop::find($request->shop_id);
        $shop->top_banner_image     = $request->top_banner_image;
        $shop->top_banner_link      = $request->top_banner_link;
        $shop->slider_images        = $request->slider_images;
        $shop->slider_links         = $request->slider_links;
        $shop->banner_full_width_1_images   = $request->banner_full_width_1_images;
        $shop->banner_full_width_1_links    = $request->banner_full_width_1_links;
        $shop->banners_half_width_images    = $request->banners_half_width_images;
        $shop->banners_half_width_links     = $request->banners_half_width_links;
        $shop->banner_full_width_2_images   = $request->banner_full_width_2_images;
        $shop->banner_full_width_2_links    = $request->banner_full_width_2_links;
        if ($shop->save()) {
            flash(translate('Your Shop banners has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function verify_form()
    {
        $shop = Auth::user()->shop;
        $submission = VendorKycSubmission::firstOrNew([
            'user_id' => Auth::id(),
        ]);

        return view('seller.verify_form', compact('shop', 'submission'));
    }

    public function verify_form_store(Request $request)
    {
        $request->validate([
            'id_type' => 'required|string|max:255',
            'id_number' => 'required|string|max:255',
            'id_document' => 'required|string',
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'cac_number' => 'nullable|string|max:255',
            'store_photos' => 'nullable|string',
        ]);

        $storePhotos = $request->filled('store_photos') ? array_filter(explode(',', $request->store_photos)) : [];
        $documents = [
            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'id_document' => $request->id_document,
            'cac_number' => $request->cac_number,
            'bank' => [
                'name' => $request->bank_name,
                'account_name' => $request->bank_account_name,
                'account_number' => $request->bank_account_number,
            ],
            'store_photos' => array_values($storePhotos),
        ];

        VendorKycSubmission::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'documents' => $documents,
                'status' => 'pending',
                'rejection_notes' => null,
            ]
        );

        $shop = Auth::user()->shop;
        $shop->verification_info = json_encode($documents);
        $shop->verification_status = 0;
        $shop->save();
        Cache::forget('verified_sellers_id');

        $admins = User::where('user_type', 'admin')->get();
        if ($admins->isNotEmpty()) {
            $notificationData = [
                'shop' => $shop,
                'status' => 'submitted',
                'notification_type_id' => get_notification_type('shop_verify_request_submitted', 'type')->id,
            ];
            Notification::send($admins, new ShopVerificationNotification($notificationData));
        }

        flash(translate('Your KYC submission has been sent for review. We will notify you once it is processed.'))->success();
        return redirect()->route('seller.dashboard');
    }

    public function show()
    {
    }

    public function categoriesWiseCommission(Request $request){
        $sort_search =null;
        $categories = Category::orderBy('order_level', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        $categories = $categories->paginate(15);
        return view('seller.categoryWise_commission', compact('categories'))->render();
    }
}
