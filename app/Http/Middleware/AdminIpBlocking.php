<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpBlocking
{
    public function __construct(
        protected AdminAuthService $adminAuth
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->adminAuth->isIpBlocked($request->ip())) {
            $remaining = $this->adminAuth->getBlockTimeRemaining($request->ip());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "IP bloquée suite à trop de tentatives. Réessayez dans {$remaining} minutes.",
                    'retry_after' => $remaining * 60,
                ], 429);
            }

            return redirect()->route('admin.login')
                ->with('error', "IP bloquée suite à trop de tentatives. Réessayez dans {$remaining} minutes.");
        }

        return $next($request);
    }
}
