<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class AdminMaintenanceController extends Controller
{
    /**
     * Display maintenance dashboard.
     */
    public function index()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
        ];

        $storage = [
            'logs' => $this->getDirectorySize(storage_path('logs')),
            'cache' => $this->getDirectorySize(storage_path('framework/cache')),
            'sessions' => $this->getDirectorySize(storage_path('framework/sessions')),
        ];

        $maintenanceMode = app()->isDownForMaintenance();

        return Inertia::render('Admin/Maintenance/Index', [
            'systemInfo' => $systemInfo,
            'storage' => $storage,
            'maintenanceMode' => $maintenanceMode,
        ]);
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');

        try {
            match ($type) {
                'config' => Artisan::call('config:clear'),
                'route' => Artisan::call('route:clear'),
                'view' => Artisan::call('view:clear'),
                'cache' => Cache::flush(),
                'all' => $this->clearAllCache(),
                default => throw new \InvalidArgumentException('Type de cache invalide'),
            };

            $messages = [
                'config' => 'Cache de configuration vidé',
                'route' => 'Cache des routes vidé',
                'view' => 'Cache des vues vidé',
                'cache' => 'Cache applicatif vidé',
                'all' => 'Tous les caches vidés',
            ];

            return back()->with('success', $messages[$type]);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                return back()->with('success', 'Mode maintenance désactivé.');
            } else {
                $secret = bin2hex(random_bytes(16));
                Artisan::call('down', ['--secret' => $secret]);
                return back()->with('success', "Mode maintenance activé. Secret: {$secret}");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * View recent logs.
     */
    public function logs()
    {
        $logs = $this->getRecentLogs(100);

        return Inertia::render('Admin/Maintenance/Logs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Clear all caches.
     */
    private function clearAllCache(): void
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Cache::flush();
    }

    /**
     * Get directory size in human readable format.
     */
    private function getDirectorySize(string $path): string
    {
        if (!File::isDirectory($path)) {
            return '0 B';
        }

        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get recent log entries.
     */
    private function getRecentLogs(int $lines = 50): array
    {
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $allLines = explode("\n", $content);
        $lastLines = array_slice($allLines, -$lines);

        $logs = [];
        $currentEntry = null;

        foreach ($lastLines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)$/', $line, $matches)) {
                if ($currentEntry) {
                    $logs[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                ];
            } elseif ($currentEntry && trim($line)) {
                $currentEntry['message'] .= "\n" . $line;
            }
        }

        if ($currentEntry) {
            $logs[] = $currentEntry;
        }

        return array_reverse(array_slice($logs, -30));
    }
}
