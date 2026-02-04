<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as BaseMiddleware;

class PreventRequestsDuringMaintenance extends BaseMiddleware
{
    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     */
    protected function inExceptArray($request): bool
    {
        // Toujours autoriser le panel admin
        $adminPrefix = config('admin.url_prefix', env('ADMIN_URL_PREFIX', 'admin'));

        if ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
