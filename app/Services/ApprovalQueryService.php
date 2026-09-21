<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\FormTemplate;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ApprovalQueryService
{
    public function pendingFor(User $user, array $filters): LengthAwarePaginator
    {
        $query = WorkflowRequest::query()
            ->with(['formTemplate', 'creator', 'currentStep.approverRole', 'currentStep.approverDepartment'])
            ->where('status', WorkflowRequest::STATUS_PENDING)
            ->whereHas('currentStep', fn (Builder $step) => $step->approverFor($user));

        $this->applyRequestFilters($query, $filters);
        $this->applySubmittedDateFilters($query, $filters);

        return $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function historyFor(User $user, array $filters): LengthAwarePaginator
    {
        $query = ApprovalHistory::query()
            ->with(['workflowRequest.formTemplate', 'workflowRequest.creator', 'step'])
            ->where('actor_id', $user->id)
            ->whereIn('action', ApprovalHistory::DECISION_ACTIONS);

        $this->applyHistoryRequestFilters($query, $filters);

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        $this->applyActionDateFilters($query, $filters);

        return $query
            ->orderByRaw('COALESCE(acted_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function formTemplates(): Collection
    {
        return FormTemplate::query()
            ->orderBy('name')
            ->orderByDesc('version')
            ->get(['id', 'name', 'version']);
    }

    private function applyRequestFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['keyword'])) {
            $keyword = $this->like($filters['keyword']);
            $query->where('request_code', 'like', $keyword);
        }

        if (! empty($filters['requester'])) {
            $requester = $this->like($filters['requester']);
            $query->whereHas('creator', function (Builder $creator) use ($requester): void {
                $creator->where(function (Builder $identity) use ($requester): void {
                    $identity->where('name', 'like', $requester)
                        ->orWhere('email', 'like', $requester);
                });
            });
        }

        if (! empty($filters['form_template_id'])) {
            $query->where('form_template_id', $filters['form_template_id']);
        }
    }

    private function applyHistoryRequestFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['keyword'])) {
            $keyword = $this->like($filters['keyword']);
            $query->whereHas('workflowRequest', fn (Builder $request) => $request->where('request_code', 'like', $keyword));
        }

        if (! empty($filters['requester'])) {
            $requester = $this->like($filters['requester']);
            $query->whereHas('workflowRequest.creator', function (Builder $creator) use ($requester): void {
                $creator->where(function (Builder $identity) use ($requester): void {
                    $identity->where('name', 'like', $requester)
                        ->orWhere('email', 'like', $requester);
                });
            });
        }

        if (! empty($filters['form_template_id'])) {
            $formTemplateId = $filters['form_template_id'];
            $query->whereHas('workflowRequest', fn (Builder $request) => $request->where('form_template_id', $formTemplateId));
        }
    }

    private function applySubmittedDateFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['from_date'])) {
            $this->whereEffectiveRequestDate($query, '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $this->whereEffectiveRequestDate($query, '<=', $filters['to_date']);
        }
    }

    private function applyActionDateFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['from_date'])) {
            $this->whereEffectiveHistoryDate($query, '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $this->whereEffectiveHistoryDate($query, '<=', $filters['to_date']);
        }
    }

    private function whereEffectiveRequestDate(Builder $query, string $operator, string $date): void
    {
        $query->where(function (Builder $dateQuery) use ($operator, $date): void {
            $dateQuery->whereDate('submitted_at', $operator, $date)
                ->orWhere(function (Builder $fallback) use ($operator, $date): void {
                    $fallback->whereNull('submitted_at')
                        ->whereDate('created_at', $operator, $date);
                });
        });
    }

    private function whereEffectiveHistoryDate(Builder $query, string $operator, string $date): void
    {
        $query->where(function (Builder $dateQuery) use ($operator, $date): void {
            $dateQuery->whereDate('acted_at', $operator, $date)
                ->orWhere(function (Builder $fallback) use ($operator, $date): void {
                    $fallback->whereNull('acted_at')
                        ->whereDate('created_at', $operator, $date);
                });
        });
    }

    private function like(string $value): string
    {
        return '%'.trim($value).'%';
    }
}
