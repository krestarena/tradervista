<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SendSmsService;
use App\Utility\EmailUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OTPVerificationController extends Controller
{
    public function verification()
    {
        return view('auth.verifyEmailOrPhone');
    }

    public function verify_phone(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'numeric'],
        ]);

        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if (!$this->isValidCode($user, $request->verification_code)) {
            return back()->withErrors(['verification_code' => translate('Invalid or expired verification code')]);
        }

        $this->markUserVerified($user);

        return redirect()->route('dashboard')->withSuccess(translate('Your phone number has been verified'));
    }

    public function resend_verificcation_code()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $this->send_code($user);

        return back()->withSuccess(translate('A new verification code has been sent'));
    }

    public function show_reset_password_form()
    {
        return view('auth.passwords.phone');
    }

    public function reset_password_with_code(Request $request)
    {
        $request->validate([
            'phone' => ['required'],
            'verification_code' => ['required', 'numeric'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user || !$this->isValidCode($user, $request->verification_code)) {
            return back()->withErrors(['verification_code' => translate('Invalid or expired verification code')]);
        }

        $user->password = Hash::make($request->password);
        $this->clearOtp($user);
        $user->save();

        return redirect()->route('login')->withSuccess(translate('Password changed successfully'));
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required'],
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return back()->withErrors(['phone' => translate('User not found')]);
        }

        $this->send_code($user);

        return redirect()->route('otp-verification-page')->withSuccess(translate('Verification code sent'));
    }

    public function otpVerificationPage()
    {
        return view('auth.verifyEmailOrPhone');
    }

    public function resendOtp($phone)
    {
        $user = User::where('phone', $phone)->first();
        if ($user) {
            $this->send_code($user);
        }

        return back()->withSuccess(translate('Verification code resent'));
    }

    public function validateOtpCode(Request $request)
    {
        $request->validate([
            'phone' => ['required'],
            'verification_code' => ['required', 'numeric'],
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            throw ValidationException::withMessages(['phone' => translate('User not found')]);
        }

        if (!$this->isValidCode($user, $request->verification_code)) {
            throw ValidationException::withMessages(['verification_code' => translate('Invalid or expired verification code')]);
        }

        $this->markUserVerified($user);

        return response()->json([
            'result' => true,
            'message' => translate('Verification successful'),
            'referral_link' => $this->referralLink($user),
        ]);
    }

    public function send_code(User $user)
    {
        $this->enforceDailyLimit($user);

        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->otp_expires_at = Carbon::now()->addMinutes((int) config('tradevista.otp_expiry_minutes', 10));
        $user->otp_last_sent_at = Carbon::now();
        $user->otp_daily_count = ($user->otp_daily_count ?? 0) + 1;
        $user->otp_daily_counted_at = Carbon::now()->toDateString();
        $user->save();

        $this->sendSms($user, $code);
        $this->sendEmail($user, $code);
    }

    public function send_order_code($order)
    {
        if ($order->user) {
            $this->send_code($order->user);
        }
    }

    protected function sendSms(User $user, int $code): void
    {
        if (!addon_is_activated('otp_system') || !$user->phone) {
            return;
        }

        $smsTemplate = SmsTemplate::where('identifier', 'phone_number_verification')->first();
        $body = $smsTemplate && $smsTemplate->status == 1
            ? str_replace(['[[code]]', '[[site_name]]'], [$code, env('APP_NAME')], $smsTemplate->sms_body)
            : translate('Your verification code is ') . $code;
        $templateId = $smsTemplate->template_id ?? null;

        (new SendSmsService())->sendSMS($user->phone, env('APP_NAME'), $body, $templateId);
    }

    protected function sendEmail(User $user, int $code): void
    {
        if (!$user->email) {
            return;
        }

        try {
            EmailUtility::email_verification_for_registration_customer(
                'email_verification_for_registration_customer',
                $user->email,
                $code
            );
        } catch (\Exception $e) {
            // Swallow mail transport errors so SMS-only flows are not blocked.
        }
    }

    protected function isValidCode(User $user, $code): bool
    {
        if ($user->verification_code != $code) {
            return false;
        }

        if ($user->otp_expires_at && Carbon::now()->greaterThan(Carbon::parse($user->otp_expires_at))) {
            return false;
        }

        return true;
    }

    protected function markUserVerified(User $user): void
    {
        $this->clearOtp($user);
        if ($user->email_verified_at === null) {
            $user->email_verified_at = Carbon::now();
        }
        $user->save();
    }

    protected function clearOtp(User $user): void
    {
        $user->verification_code = null;
        $user->otp_expires_at = null;
        $user->otp_last_sent_at = null;
    }

    protected function enforceDailyLimit(User $user): void
    {
        $limit = (int) config('tradevista.otp_daily_limit', 5);
        if ($limit <= 0) {
            return;
        }

        $today = Carbon::now()->toDateString();
        if ($user->otp_daily_counted_at !== $today) {
            $user->otp_daily_count = 0;
            $user->otp_daily_counted_at = $today;
        }

        if (($user->otp_daily_count ?? 0) >= $limit) {
            throw ValidationException::withMessages([
                'phone' => translate('Daily OTP request limit reached. Please try again tomorrow.'),
            ]);
        }
    }

    protected function referralLink(User $user): ?string
    {
        if (!$user->referral_code) {
            $user->referral_code = strtoupper(substr(md5($user->id . microtime()), 0, 8));
            $user->save();
        }

        return route('home') . '?referral_code=' . $user->referral_code;
    }
}
