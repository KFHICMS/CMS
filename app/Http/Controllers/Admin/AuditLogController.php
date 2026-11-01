<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Read-only audit trail for administrators.
 * There is intentionally no create, edit or delete action: audit entries are append-only.
 */
class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function index(Request $request): View
    {
        $f = $this->validated($request);

        $logs = $this->filteredQuery($f)
            ->with('user:id,name,role')
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $userIds = AuditLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id');

        return view('admin.audit_logs.index', [
            'filters' => $f,
            'logs' => $logs,
            'modules' => AuditLog::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'users' => User::query()->whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $f = $this->validated($request);
        $query = $this->filteredQuery($f)->with('user:id,name');

        // Exporting the audit trail is itself an auditable event.
        $this->audit->record(
            $request,
            'audit_log_exported',
            'audit_log',
            null,
            'Audit log exported as CSV.'
        );

        $filename = 'kfhi-audit-log-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['ID', 'Date and time', 'User', 'Action', 'Module', 'Record ID', 'Description', 'IP address'], ',', '"', '');

            $query->chunkById(500, function ($chunk) use ($out): void {
                foreach ($chunk as $log) {
                    fputcsv($out, [
                        $log->id,
                        $log->created_at?->format('Y-m-d H:i:s'),
                        $this->csvSafe($log->user?->name ?? 'System / removed user'),
                        $this->csvSafe($log->action),
                        $this->csvSafe($log->module),
                        $log->record_id,
                        $this->csvSafe($log->description),
                        $log->ip_address,
                    ], ',', '"', '');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'module' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function filteredQuery(array $f): Builder
    {
        return AuditLog::query()
            ->when($f['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                return $q->where(fn ($w) => $w
                    ->where('description', 'like', $like)
                    ->orWhere('action', 'like', $like)
                    ->orWhere('module', 'like', $like));
            })
            ->when($f['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($f['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($f['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($f['module'] ?? null, fn ($q, $m) => $q->where('module', $m))
            ->when($f['action'] ?? null, fn ($q, $a) => $q->where('action', $a));
    }

    /** Stop spreadsheet apps from running cell values that start with = + - @ as formulas. */
    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}