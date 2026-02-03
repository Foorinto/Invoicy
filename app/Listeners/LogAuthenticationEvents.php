<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class LogAuthenticationEvents
{
    /**
     * Handle user login event.
     */
    public function handleLogin(Login $event): void
    {
        AuditLogger::logLogin();
    }

    /**
     * Handle failed login event.
     */
    public function handleFailed(Failed $event): void
    {
        AuditLogger::logLoginFailed($event->credentials['email'] ?? null);
    }

    /**
     * Handle user logout event.
     */
    public function handleLogout(Logout $event): void
    {
        AuditLogger::logLogout();
    }

    /**
     * Handle password reset event.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        AuditLogger::logPasswordChanged();
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Logout::class => 'handleLogout',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
