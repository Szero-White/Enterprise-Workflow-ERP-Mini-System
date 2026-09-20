<?php

namespace App\Support\Audit;

use Illuminate\Support\Str;

class AuditLogPresenter
{
    public function actionLabel(string $action, ?string $description = null): string
    {
        $key = 'ui.audit_actions.'.str_replace('.', '_', $action);
        $fallback = Str::ucfirst(str_replace(['.', '_'], ' ', $action));

        if ($description && $description !== $fallback) {
            return $description;
        }

        if (trans()->has($key)) {
            return __($key);
        }

        return $description ?: $fallback;
    }

    public function targetLabel(?string $auditableType): string
    {
        if (! $auditableType) {
            return '—';
        }

        $basename = class_basename($auditableType);
        $key = 'ui.audit_targets.'.$basename;

        return trans()->has($key) ? __($key) : Str::headline($basename);
    }
}
