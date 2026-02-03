<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AuditLogController extends Controller
{
    /**
     * Display the audit logs page.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = AuditLog::where('user_id', auth()->id())
            ->with('auditable')
            ->orderByDesc('created_at');

        // Filter by action category
        if ($request->filled('category')) {
            $query->ofCategory($request->category);
        }

        // Filter by specific action
        if ($request->filled('action')) {
            $query->ofAction($request->action);
        }

        // Filter by date range
        if ($request->filled('from') || $request->filled('to')) {
            $query->betweenDates($request->from, $request->to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->ofStatus($request->status);
        }

        // Search in metadata
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_EXTRACT(metadata, '$') LIKE ?", ["%{$search}%"]);
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Transform for display
        $logs->through(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'action_label' => $log->action_label,
                'action_emoji' => $log->action_emoji,
                'auditable_type' => $log->auditable_type ? class_basename($log->auditable_type) : null,
                'auditable_id' => $log->auditable_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'changed_fields' => $log->changed_fields,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'status' => $log->status,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                'created_at_human' => $log->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['category', 'action', 'from', 'to', 'status', 'search']),
            'categories' => $this->getCategories(),
        ]);
    }

    /**
     * Export audit logs as CSV.
     */
    public function export(Request $request): Response
    {
        $query = AuditLog::where('user_id', auth()->id())
            ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('category')) {
            $query->ofCategory($request->category);
        }
        if ($request->filled('from') || $request->filled('to')) {
            $query->betweenDates($request->from, $request->to);
        }
        if ($request->filled('status')) {
            $query->ofStatus($request->status);
        }

        $logs = $query->limit(10000)->get();

        $csv = $this->generateCsv($logs);

        $filename = 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get a specific audit log detail.
     */
    public function show(AuditLog $auditLog): array
    {
        // Ensure user can only view their own logs
        if ($auditLog->user_id !== auth()->id()) {
            abort(403);
        }

        return [
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'action_label' => $auditLog->action_label,
            'action_emoji' => $auditLog->action_emoji,
            'auditable_type' => $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null,
            'auditable_id' => $auditLog->auditable_id,
            'old_values' => $auditLog->old_values,
            'new_values' => $auditLog->new_values,
            'changed_fields' => $auditLog->changed_fields,
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'status' => $auditLog->status,
            'metadata' => $auditLog->metadata,
            'created_at' => $auditLog->created_at->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Get available categories for filtering.
     */
    protected function getCategories(): array
    {
        return [
            ['value' => 'auth', 'label' => 'Authentification'],
            ['value' => 'Invoice', 'label' => 'Factures'],
            ['value' => 'Client', 'label' => 'Clients'],
            ['value' => 'Quote', 'label' => 'Devis'],
            ['value' => 'Expense', 'label' => 'Dépenses'],
            ['value' => 'TimeEntry', 'label' => 'Suivi du temps'],
            ['value' => 'BusinessSettings', 'label' => 'Paramètres'],
            ['value' => 'export', 'label' => 'Exports'],
        ];
    }

    /**
     * Generate CSV content from logs.
     */
    protected function generateCsv($logs): string
    {
        $output = fopen('php://temp', 'r+');

        // UTF-8 BOM for Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Header row
        fputcsv($output, [
            'Date',
            'Action',
            'Type de ressource',
            'ID ressource',
            'Statut',
            'Adresse IP',
            'Navigateur',
        ], ';');

        // Data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->action_label,
                $log->auditable_type ? class_basename($log->auditable_type) : '',
                $log->auditable_id ?? '',
                $log->status,
                $log->ip_address ?? '',
                $this->parseUserAgent($log->user_agent),
            ], ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Parse user agent to a simple readable format.
     */
    protected function parseUserAgent(?string $userAgent): string
    {
        if (!$userAgent) {
            return '';
        }

        // Simple parsing - extract browser and OS
        $browser = 'Inconnu';
        $os = '';

        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        }

        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        }

        return trim("{$browser} / {$os}", ' /');
    }
}
