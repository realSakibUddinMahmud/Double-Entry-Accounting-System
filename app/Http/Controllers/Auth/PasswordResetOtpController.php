<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Helpers\SMSHelperElite;
use DB;
use Hash;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PasswordResetOtpController extends Controller
{
    /**
     * Show password reset request form
     */
    public function showResetRequestForm()
    {
        return view('auth.passwords.reset-request');
    }

    /**
     * Show OTP verification form
     */
    public function showVerifyOtpForm(Request $request)
    {
        $phone = $request->session()->get('phone')
            ?? $request->cookie('otp_phone')
            ?? $request->phone;

        if (!$phone) {
            return redirect()->route('password.reset.request.form')
                ->with('error', 'Session expired. Please request a new OTP.');
        }

        return view('auth.passwords.verify-otp', ['phone' => $phone]);
    }

    /**
     * Show password update form
     */
    public function showUpdatePasswordForm(Request $request)
    {
        //    return 'here';
        $phone = $request->session()->get('phone')
            ?? $request->cookie('otp_phone')
            ?? $request->phone;

        return view('auth.passwords.update-otp', ['phone' => $phone]);
    }

    /**
     * Initiate password reset via OTP
     */
    public function requestPasswordReset(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        try {
            $phoneNumber = $request->phone ?? null;
            if (strpos($phoneNumber, "+88") === 0) {
                $phoneNumber = substr($phoneNumber, 3);
            }

            // Check if user exists
            $user = DB::connection('landlord')->table('users')
                ->where('phone', $phoneNumber)
                ->first();

            if (!$user) {
                return back()->withErrors(['phone' => 'This number is not registered']);
            }

            // Check for existing unexpired OTP
            $existingOtp = DB::connection('landlord')->table('user_otps')
                ->where('phone', $phoneNumber)
                ->where('purpose', 'password_reset')
                ->where('expires_at', '>', now())
                ->orderByDesc('expires_at')
                ->first();

            if ($existingOtp) {
                return back()->withErrors(['phone' => 'You already have a valid OTP. Please wait until it expires.']);
            }

            // Checks if user requested too many OTPs
            $otpCount = DB::connection('landlord')->table('user_otps')
                ->where('phone', $phoneNumber)
                ->where('purpose', 'password_reset')
                ->where('expires_at', '<=', now())
                ->count();

            if ($otpCount > 3) {
                return back()->withErrors(['error' => 'You have exceeded the maximum number of OTP requests. Please contact your company superadmin.']);
            }

            // Generate 5-digit OTP
            $otp = rand(10000, 99999);
            $otpExpiresAt = now()->addMinutes(3);

            // Store OTP
            DB::connection('landlord')->table('user_otps')->insert([
                'phone' => $phoneNumber,
                'purpose' => 'password_reset',
                'otp' => $otp,
                'user_id' => $user->id,
                'expires_at' => $otpExpiresAt,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Send OTP via SMS
            $message = "(RyoGas) Your password reset OTP is: $otp. Valid for 3 minutes.";
            SMSHelperElite::singleSms($phoneNumber, $message);

            return redirect()->route('password.verify.otp.form')
                ->with('phone', $phoneNumber)
                ->withCookie(cookie('otp_phone', $phoneNumber, 10)); // 10 minutes

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Password reset failed. Please try again.']);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        // \Log::info('OTP Verification Attempt:', [
        //     'phone' => $request->phone,
        //     'otp' => $request->otp,
        //     'all_input' => $request->all()
        // ]);

        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|numeric|digits:5'
        ]);

        try {
            $phoneNumber = $request->phone ?? null;
            if (strpos($phoneNumber, "+88") === 0) {
                $phoneNumber = substr($phoneNumber, 3);
            }

            $otpRecord = DB::connection('landlord')->table('user_otps')
                ->where('phone', $phoneNumber)
                ->where('purpose', 'password_reset')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpRecord || $otpRecord->otp != $request->otp || now()->gt($otpRecord->expires_at)) {
                return redirect()->back()->withInput()->withErrors(['otp' => 'Invalid or expired OTP']);
            }

            return redirect()->route('password.update.form')->with([
                'phone' => $phoneNumber,
                'status' => 'OTP verified successfully'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'OTP verification failed']);
        }
    }

    /**
     * Update password with OTP
     */
    public function updatePasswordWithOtp(Request $request)
    {
        try {
            $phoneNumber = $request->phone ?? null;

            // Validate phone number
            if (empty($phoneNumber)) {
                return back()->withErrors(['error' => 'Phone number is required']);
            }

            // Remove +88 prefix if present
            if (strpos($phoneNumber, "+88") === 0) {
                $phoneNumber = substr($phoneNumber, 3);
            }

            // Validate password presence and length
            $newPassword = $request->new_password ?? '';
            $confirmPassword = $request->new_password_confirmation ?? '';

            if (strlen($newPassword) < 8) {
                return back()->withErrors(['error' => 'Password must be at least 8 characters']);
            }

            // Validate password match
            if ($newPassword !== $confirmPassword) {
                return back()->withErrors(['error' => 'Password confirmation does not match']);
            }

            $otpRecord = DB::connection('landlord')->table('user_otps')
                ->where('phone', $phoneNumber)
                ->where('purpose', 'password_reset')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpRecord || now()->gt($otpRecord->expires_at)) {
                return back()->withErrors(['error' => 'Invalid or expired OTP']);
                // throw new \Exception('Invalid or expired OTP');
            }

            $hashedPassword = Hash::make($request->new_password);

            // Update landlord DB
            DB::connection('landlord')->table('users')
                ->where('id', $otpRecord->user_id)
                ->update(['password' => $hashedPassword]);

            // Update tenant DB
            DB::connection('tenant')->table('users')
                ->where('id', $otpRecord->user_id)
                ->update(['password' => $hashedPassword]);


            // Clean up OTP
            DB::connection('landlord')->table('user_otps')
                ->where('phone', $phoneNumber)
                ->where('purpose', 'password_reset')
                ->delete();

            return redirect()->route('login')->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Password update failed']);
            // return back()->withErrors(['error' => 'Password update failed: ' . $e->getMessage()]);
        }
    }
}
