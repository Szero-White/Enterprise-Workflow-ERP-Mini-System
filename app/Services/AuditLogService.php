<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(string $action, ?Model $model = null, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): void
    {
        AuditLog::create([
            'actor_id' => Auth::id(),
            'action' => $action,
            'description' => $description ?? $this->descriptionForAction($action),
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    private function descriptionForAction(string $action): string
    {
        $key = 'ui.audit_actions.'.str_replace('.', '_', $action);

        if (trans()->has($key, config('app.locale'))) {
            return __($key, [], config('app.locale'));
        }

        return ucfirst(str_replace(['.', '_'], ' ', $action));
    }
}
