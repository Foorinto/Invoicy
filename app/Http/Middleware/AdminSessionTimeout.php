<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    public function __construct(
        protected AdminAuthService $adminAuth
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $this->adminAuth->getSession($request);

        if (!$session) {
            return redirect()->route('admin.login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        // Check if session has expired
        if ($session->isExpired()) {
            $this->adminAuth->destroySession($request);
            return redirect()->route('admin.login')
                ->with('error', 'Session expirée après inactivité. Veuillez vous reconnecter.');
        }

        // Touch the session to update last activity
        $this->adminAuth->touchSession($session);

        return $next($request);
    }
}
