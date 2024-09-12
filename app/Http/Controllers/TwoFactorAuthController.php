<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Google2FA;

class TwoFactorAuthController extends Controller
{
    /**
     * Show the 2FA setup form.
     *
     * This function generates a QR code and secret key for 2FA setup. If the user
     * already has a secret key, it retrieves it. Otherwise, it generates a new one.
     * It also generates 10 recovery codes and stores them in the user's database
     * record.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View The view displaying the
     *     QR code, secret code, and recovery codes.
     */
    public function show2FASetupForm(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Initialize the Google2FA class
        $google2fa = app('pragmarx.google2fa');

        // Check if the user already has a secret key
        if ($user->google2fa_secret != null) {
            // If the user already has a secret key, use it
            $secret = $user->google2fa_secret;
        } else {
            // If the user doesn't have a secret key, generate a new one
            $secret = $google2fa->generateSecretKey();
        }

        // Generate the QR code
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret,
        );

        // Generate 10 recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $recoveryCodes[] = Str::random(16);
        }

        // Store the recovery codes in the user's database record
        $user->google2fa_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        // Send the QR code, secret code, and recovery codes to the view
        return view('2fa_qr_code', [
            'QR_Image' => $QR_Image,
            'secret' => $secret,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }


    /**
     * Store the user's one-time password and secret code for Two-Factor Authentication.
     *
     * @param  Request  $request  The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store2FA(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'one_time_password' => 'required|string|numeric',
            'secret' => 'required|string',
        ]);

        // Get the user's Google Authenticator secret from the database
        $user = Auth::user();
        $secret = $request->secret;

        // Create a new instance of the Google2FA class
        $google2fa = app('pragmarx.google2fa');

        // Verify the one-time password
        $valid = $google2fa->verifyKey($secret, $request->one_time_password);

        if ($valid) {
            // OTP is valid, mark the user as using 2FA
            $user->two_factor_enabled  = true;
            $user->google2fa_secret = $secret;
            $user->save();

            // Set session variables for verification progress and success
            session()->put('verification_in_progress', true);
            $request->session()->put('2fa_verified', true);

            // Redirect to the dashboard with a success message
            return redirect()
                ->route('dashboard')
                ->with('success', __('messages.2fa_enabled_successfully'));
        }
        else {
        // OTP is not valid, set session variable for error message
        $request->session()->put('2fa_verified', false);
        }
        // Redirect back with an error message
        return redirect()
            ->back()
            ->with('error', __('messages.invalid_verification_code'));
    }


    /**
     * Show the form for verifying the user's one-time password and secret code.
     *
     * This function checks if the user has a Google Authenticator secret code.
     * If the user has a secret code, it is used. Otherwise, a new secret code
     * is generated using the Google2FA class. The secret code is then passed
     * to the view for rendering.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View The view displaying
     *     the form for verifying the user's one-time password and secret code.
     */
    public function showVerifyForm()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Check if the user has a Google Authenticator secret code
        if ($user->google2fa_secret) {
            // If the user has a secret code, use it
            $secret = $user->google2fa_secret;
        } else {
            // If the user doesn't have a secret code, generate a new one
            $google2fa = app('pragmarx.google2fa');
            $secret = $google2fa->generateSecretKey();
        }

        // Pass the secret code to the view for rendering
        return view('auth.2fa_login', ['secret' => $secret]);
    }


    /**
     * Verify the user's one-time password and secret code.
     *
     * This function validates the one-time password input, retrieves the user ID
     * and authentication attempt from the session, finds the user by ID, checks if
     * the user exists and 2FA is enabled, verifies the OTP using the Google2FA
     * library, and redirects to the dashboard with a success message if the OTP
     * is valid, or redirects back with an error message if the OTP is invalid.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse  The redirect response.
     */
    public function verify(Request $request)
    {
        // Validate the one-time password input
        $request->validate([
            'one_time_password' => 'required|string|numeric',
        ]);

        // Retrieve user ID and authentication attempt from the session
        $user_id = $request->session()->get('2fa:user:id');
        $remember = $request->session()->get('2fa:auth:remember', false);
        $attempt = $request->session()->get('2fa:auth:attempt', false);

        // Verify user ID and authentication attempt
        if (!$user_id || !$attempt) {
            // Redirect if session data is missing
            return redirect()->route('login');
        }

        // Find user by ID
        $user = User::find($user_id);

        // Check if user exists and 2FA is enabled
        if (!$user) {
            // Redirect if user or 2FA not enabled
            return redirect()->route('login');
        }

        // Verify OTP using Google2FA library
        $google2fa = app('pragmarx.google2fa');
        $otp_secret = $user->google2fa_secret;
        $otp = $request->one_time_password;

        // Verify the OTP against the secret stored in the database
        $valid = $google2fa->verifyKey($otp_secret, $otp);

        if ($valid) {
            // Redirect to the dashboard with a success message
            $request->session()->put('2fa_verified', true);
            return redirect()
                ->route('dashboard')
                ->with('success', __('messages.logged_in_successfully'));
        }
        else {
        $request->session()->put('2fa_verified', false);
        }
        // Redirect back with an error message
        return redirect()
            ->back()
            ->with(['message' => __('messages.invalid_verification_code')]);
    }


    /**
     * Verify a recovery code for two-factor authentication.
     *
     * This function validates the recovery code input, retrieves the user from the session,
     * decodes the user's recovery codes from JSON, checks if the provided recovery code matches
     * any stored recovery codes, logs in the user if a match is found, and redirects to the dashboard
     * or intended page with a success message. If no match is found, it redirects back to the login page
     * with an error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object containing the recovery code.
     * @return \Illuminate\Http\RedirectResponse The redirect response to the dashboard or intended page
     *                                        with a success message if the recovery code is valid,
     *                                        or back to the login page with an error message if the recovery
     *                                        code is invalid.
     */
    public function verifyRecovery(Request $request)
    {
        // Validate the recovery code input
        $request->validate([
            'recovery_code' => 'required|string|size:16',
        ]);

        // Get the logged-in user
        $user = Auth::user();

        // Decode the user's recovery codes from JSON
        $recoveryCodes = json_decode($user->google2fa_recovery_codes, true);

        // Check if the recovery codes array is properly formatted
        if ($recoveryCodes === null) {
            // Redirect to the login page with an error message if the recovery codes are not properly formatted
            return redirect()->route('login')->withErrors(['recovery_code' => __('messages.recovery_codes_not_properly_formatted')]);
        }

        // Check if the provided recovery code matches any stored recovery codes
        if (in_array($request->input('recovery_code'), $recoveryCodes)) {
            // If a match is found, log in the user
            $request->session()->put('2fa_verified', true);
            Auth::login($user);

            // Optionally, remove the used recovery code or replace it with a new one
            $recoveryCodes = array_diff($recoveryCodes, [$request->input('recovery_code')]);
            $user->google2fa_recovery_codes = json_encode(array_values($recoveryCodes)); // Re-index the array
            $user->save();

            // Redirect to the dashboard or intended page with a success message
            return redirect()->route('dashboard')->with('success', __('messages.logged_in_successfully'));
        } else {
            // If no match is found, redirect back to the login page with an error message
            $request->session()->put('2fa_verified', false);
            return redirect()->back()->with(['message' => __('messages.invalid_recovery_code')]);
        }
    }
}
