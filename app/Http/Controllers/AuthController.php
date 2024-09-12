<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * Show the login form based on 2FA verification status.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showLoginForm()
    {
        // Display the login form view.
        return view('auth.login');
    }


    /**
     * Handle the login request.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse The redirect response.
     */
    public function login(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Define custom error messages
        $validator->setAttributeNames([
            'email' => __('messages.email'),
            'password' => __('messages.password'),
        ]);

        // Validate input
        if ($validator->fails()) {
            // Pass validation errors to the view and retain user input on redirect
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // hCaptcha validation
        // $hCaptchaResponse = $request->input('h-captcha-response');

        // if (!$hCaptchaResponse) {
        //     return redirect()->back()->withErrors(['hcaptcha' => 'hCaptcha is required']);
        // }

        // $secretKey = env('HCAPTCHA_SECRET_KEY'); // Replace with your actual secret key
        // $verifyResponse = Http::post('https://hcaptcha.com/siteverify', [
        //     'secret' => $secretKey,
        //     // 'response' => $hCaptchaResponse,
        //     'remoteip' => $request->ip(),
        // ]);

        // $verifyData = json_decode($verifyResponse->body(), true);

        // if (!$verifyData['success']) {
        //     return redirect()->back()->withErrors(['hcaptcha' => 'hCaptcha verification failed']);
        // }

        // Attempt authentication
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // Get the authenticated user
            $user = $request->user();

            // Set the 2FA verification status to false
            session()->put('2fa_verified', false);

            // Redirect to 2FA setup if not already set up
            if (!$user->google2fa_secret) {
                // Store the user ID and authentication attempt in the session
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:auth:attempt', true);

                // Redirect to the 2FA setup route
                return redirect()->route('2fa.setup');
            } else {
                // Store the user ID and authentication attempt in the session
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:auth:attempt', true);

                // Redirect to the 2FA verification route
                return redirect()->route('2fa.verify');
            }
        }

        // Authentication failed
        // Add specific error message for login failure
        return redirect()->back()
            ->withErrors(['login_error' => __('messages.login_failure')])
            ->withInput($request->only('email')); // Retain email input on redirect
    }


    /**
     * Show the registration form.
     *
     * This function renders the registration form view. It does not perform any
     * validation or processing of user input. The registration form view can be
     * found in the 'auth.register' view file.
     *
     * @return \Illuminate\Contracts\Support\Renderable The registration form view.
     */
    public function showRegistrationForm()
    {
        // Render the registration form view
        return view('auth.register');
    }


    /**
     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse The redirect response.
     */
    public function register(Request $request)
    {
        // Validate the user input
        $request->validate([
            'name' => 'required|string|max:255', // Name is required and must be a string with a maximum length of 255 characters
            'email' => 'required|string|email|max:255|unique:users', // Email is required, must be a string, must be a valid email, and must be unique in the users table
            'password' => 'required|string|min:8|confirmed', // Password is required and must be a string with a minimum length of 8 characters and must match the password confirmation field
            'recaptcha_response' => 'required' // reCAPTCHA response is required
        ], [
            'name.required' => __('messages.name_required'), // Custom error message for the name field when it is empty
            'email.required' => __('messages.email_required'), // Custom error message for the email field when it is empty
            'email.email' => __('messages.email_invalid'), // Custom error message for the email field when it is not a valid email
            'email.unique' => __('messages.email_taken'), // Custom error message for the email field when it is already used by another user
            'password.required' => __('messages.password_required'), // Custom error message for the password field when it is empty
            'password.min' => __('messages.password_min_length'), // Custom error message for the password field when it is shorter than the minimum length
            'password.confirmed' => __('messages.password_confirmation_mismatch'), // Custom error message for the password field when the password confirmation does not match the password field
            'recaptcha_response.required' => __('messages.recaptcha_required') // Custom error message for the reCAPTCHA response when it is empty
        ]);

        // Verify reCAPTCHA
        $recaptchaResponse = $request->input('recaptcha_response');
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => '6Lf8LBAqAAAAAGxlFi1ygQLtTqho3ZLQG_Ik9a8G',
            'response' => $recaptchaResponse,
        ]);

        $recaptchaData = $response->json();
        \Log::debug($recaptchaData['score']);

        if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
            // reCAPTCHA verification failed or the score is too low
            \Log::debug('recaptcha_failed!');
            return redirect()->back()->withErrors(['recaptcha' => __('messages.recaptcha_failed')])->withInput();
        }

        // Create a new user with the validated input
        User::create([
            'name' => $request->name, // Name of the user
            'email' => $request->email, // Email of the user
            'password' => Hash::make($request->password), // Hashed password of the user
        ]);

        // Redirect to the login page with a success message
        return redirect()->route('login')->with('success', __('messages.registration_success'));
    }


    /**
     * Logs out the user by invalidating the session, regenerating the session token,
     * and redirecting to the login page.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the login page.
     */
    public function logout()
    {
        // Log out the user by invalidating the session
        Auth::logout();

        // Invalidate the current session
        session()->invalidate();

        // Regenerate the session token to prevent session fixation attacks
        session()->regenerateToken();

        // Redirect the user to the login page
        return redirect()->route('login');
    }


    /**
     * Verifies the user's password and redirects to the recovery codes page if the password is correct.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object containing the user's password.
     * @return \Illuminate\Http\RedirectResponse The redirect response to the recovery codes page if the password is correct,
     *                                           or back with an error message if the password is incorrect.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => __('messages.password_incorrect')]);
        }
    }


    /**
     * Retrieves the recovery codes for the authenticated user and displays them in the 'recovery-codes' view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View The view displaying the recovery codes.
     */
    public function showRecoveryCodes()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Decode the user's recovery codes from JSON
        $recoveryCodes = json_decode($user->google2fa_recovery_codes, true);

        // Render the 'recovery-codes' view with the recovery codes
        return view('recovery-codes', compact('recoveryCodes'));
    }


    /**
     * Regenerates the recovery codes for the authenticated user and displays the updated recovery codes.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View The view displaying the updated recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Generate 10 new recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $recoveryCodes[] = Str::random(16);
        }

        // Update the user's recovery codes in the database
        $user->google2fa_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        // Render the 'recovery-codes' view with the updated recovery codes
        return view('recovery-codes', compact('recoveryCodes'));
    }
}
