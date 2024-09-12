<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{

    /**
     * Handle an incoming request.
     *
     * This middleware ensures that the user has two-factor authentication enabled and has been verified.
     * If the user does not have two-factor authentication enabled, it redirects them to the two-factor authentication setup page.
     * If the user has two-factor authentication enabled but has not been verified, it redirects them to the two-factor authentication verification page.
     *
     * @param Request $request The incoming request.
     * @param Closure $next The next middleware in the stack.
     * @return Response The response.
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Get the logged-in user
        $user = Auth::user();

        if ($user) {

            // Check if the user has two-factor authentication enabled and has been verified
            $twoFactorEnabled = $user->two_factor_enabled;
            $twoFactorVerified = session()->get('2fa_verified') === true;

            // Redirect to the two-factor authentication setup page if the user does not have two-factor authentication enabled
            if (!$twoFactorEnabled && !$twoFactorVerified && request()->route()->getName() !== '2fa.setup') {
                Log::debug('4');
                return redirect()->route('2fa.setup');
            }

            // Redirect to the two-factor authentication verification page if the user has two-factor authentication enabled but has not been verified
            if ($twoFactorEnabled && $twoFactorVerified === false && request()->route()->getName() !== '2fa.verify') {
                Log::debug('3');
                return redirect()->route('2fa.verify');
            }

            if ($twoFactorEnabled && $twoFactorVerified && request()->route()->getName() !== 'dashboard') {
                // Redirect to the dashboard if two-factor authentication is enabled and verified
                Log::debug('A');
                return redirect()->route('dashboard');
            }
        }

        // Continue to the next middleware in the stack
        return $next($request);
    }
}
