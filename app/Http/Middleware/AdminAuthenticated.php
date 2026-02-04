<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticated
{
    public function __construct(
        protected AdminAuthService $adminAuth
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if IP is blocked
        if ($this->adminAuth->isIpBlocked($request->ip())) {
            $remaining = $this->adminAuth->getBlockTimeRemaining($request->ip());
            abort(403, "IP bloquée. Réessayez dans {$remaining} minutes.");
        }

        // Check if fully authenticated (login + 2FA)
        if ($this->adminAuth->isAuthenticated($request)) {
            return $next($request);
        }

        // Check if pending 2FA
        if ($this->adminAuth->isPendingTwoFactor($request)) {
            return redirect()->route('admin.2fa');
        }

        // Not authenticated at all
        return redirect()->route('admin.login');
    }
}
