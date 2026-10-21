<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Record an audit entry for the authenticated user's request.
     *
     * Descriptions must never contain passwords, QR tokens, scanned values,
     * child codes typed by the user, or free-text field notes.
     */
    public function record(
        Request $request,
        string $action,
        string $module,
        ?int $recordId,
        string $description
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}