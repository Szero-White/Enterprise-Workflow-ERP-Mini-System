<?php

namespace App\Services\Notifications;

use App\Enums\PurchaseRequestFulfillmentRoute;
use App\Models\GoodsReceipt;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Support\Forms\DynamicFieldValuePresenter;
use App\Support\Notifications\NotificationContent;
use Illuminate\Support\Str;

class NotificationContentBuilder
{
    public function __construct(private DynamicFieldValuePresenter $valuePresenter) {}

    public function pendingApproval(WorkflowRequest $workflowRequest, string $event): NotificationContent
    {
        $context = $this->workflowContext($workflowRequest);
        $formName = $context['form_name'];
        $requester = $context['requester_name'];

        return new NotificationContent(
            title: __('messages.notification_workflow_pending_title', ['form' => $formName]),
            message: match ($event) {
                'resubmitted' => __('messages.notification_workflow_pending_resubmitted_body', ['requester' => $requester]),
                'forwarded' => __('messages.notification_workflow_pending_forwarded_body', ['requester' => $requester]),
                default => __('messages.notification_workflow_pending_submitted_body', ['requester' => $requester]),
            },
            data: $context + [
                'action' => 'pending_approval',
                'destination' => 'approval',
            ],
        );
    }

    public function approved(WorkflowRequest $workflowRequest, ?User $actor): NotificationContent
    {
        $context = $this->workflowContext($workflowRequest);

        return new NotificationContent(
            title: __('messages.notification_workflow_approved_title', ['form' => $context['form_name']]),
            message: __('messages.notification_workflow_approved_body', ['actor' => $actor?->name ?? __('ui.unknown_user')]),
            data: $context + [
                'action' => 'approved',
                'destination' => 'employee_request',
            ],
        );
    }

    public function rejected(WorkflowRequest $workflowRequest, ?User $actor, string $reason): NotificationContent
    {
        $context = $this->workflowContext($workflowRequest);

        return new NotificationContent(
            title: __('messages.notification_workflow_rejected_title', ['form' => $context['form_name']]),
            message: __('messages.notification_workflow_rejected_body', [
                'actor' => $actor?->name ?? __('ui.unknown_user'),
                'reason' => $reason,
            ]),
            data: $context + [
                'action' => 'rejected',
                'destination' => 'employee_request',
            ],
        );
    }

    public function returned(WorkflowRequest $workflowRequest, ?User $actor, string $reason): NotificationContent
    {
        $context = $this->workflowContext($workflowRequest);

        return new NotificationContent(
            title: __('messages.notification_workflow_returned_title', ['form' => $context['form_name']]),
            message: __('messages.notification_workflow_returned_body', [
                'actor' => $actor?->name ?? __('ui.unknown_user'),
                'reason' => $reason,
            ]),
            data: $context + [
                'action' => 'returned',
                'destination' => 'employee_request',
            ],
        );
    }

    public function purchaseRequestReady(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): NotificationContent
    {
        $context = $this->purchaseRequestContext($workflowRequest, $purchaseRequest);

        return new NotificationContent(
            title: __('messages.notification_purchase_request_ready_title'),
            message: __('messages.notification_purchase_request_ready_professional_body', [
                'requester' => $context['requester_name'],
            ]),
            data: $context + [
                'action' => 'create_purchase_order',
                'destination' => 'purchase_request',
            ],
        );
    }

    public function purchaseRequestStockReady(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): NotificationContent
    {
        $context = $this->purchaseRequestContext($workflowRequest, $purchaseRequest);

        return new NotificationContent(
            title: __('messages.notification_purchase_request_stock_ready_title'),
            message: __('messages.notification_purchase_request_stock_ready_professional_body', [
                'requester' => $context['requester_name'],
            ]),
            data: $context + [
                'action' => 'fulfill_from_stock',
                'destination' => 'purchase_request',
            ],
        );
    }

    public function purchaseRequestStockFulfilled(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): NotificationContent
    {
        $context = $this->purchaseRequestContext($workflowRequest, $purchaseRequest);

        return new NotificationContent(
            title: __('messages.notification_purchase_request_stock_fulfilled_title'),
            message: __('messages.notification_purchase_request_stock_fulfilled_professional_body'),
            data: $context + [
                'action' => 'stock_fulfilled',
                'destination' => 'employee_request',
            ],
        );
    }

    public function assetsReady(GoodsReceipt $receipt, int $assetCount): NotificationContent
    {
        $receipt->loadMissing(['purchaseOrder', 'warehouse']);

        return new NotificationContent(
            title: __('messages.notification_assets_ready_title'),
            message: __('messages.notification_assets_ready_professional_body', [
                'count' => $assetCount,
                'warehouse' => $receipt->warehouse?->name ?? '—',
            ]),
            data: [
                'goods_receipt_id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'purchase_order_id' => $receipt->purchase_order_id,
                'po_number' => $receipt->purchaseOrder?->po_number,
                'asset_count' => $assetCount,
                'warehouse_id' => $receipt->warehouse_id,
                'warehouse_name' => $receipt->warehouse?->name,
                'details' => [
                    ['label' => __('ui.notification_details.receipt'), 'value' => $receipt->receipt_number],
                    ['label' => __('ui.notification_details.warehouse'), 'value' => $receipt->warehouse?->name ?? '—'],
                    ['label' => __('ui.notification_details.asset_count'), 'value' => (string) $assetCount],
                ],
                'action' => 'review_ready_assets',
                'destination' => 'assets',
            ],
        );
    }

    private function workflowContext(WorkflowRequest $workflowRequest): array
    {
        $workflowRequest->loadMissing([
            'creator',
            'formTemplate.fields',
            'values.field',
        ]);

        return [
            'request_id' => $workflowRequest->id,
            'request_code' => $workflowRequest->request_code,
            'status' => $workflowRequest->status,
            'form_name' => $workflowRequest->formTemplate?->name ?? __('ui.request'),
            'requester_id' => $workflowRequest->creator?->id,
            'requester_name' => $workflowRequest->creator?->name ?? __('ui.unknown_user'),
            'details' => $this->dynamicRequestDetails($workflowRequest),
        ];
    }

    private function purchaseRequestContext(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): array
    {
        $workflowRequest->loadMissing('creator');
        $purchaseRequest->loadMissing('items');

        $itemSummary = $purchaseRequest->items
            ->take(2)
            ->map(fn ($line) => sprintf('%s × %s', $line->item_name, $this->trimNumber($line->requested_quantity)))
            ->implode(', ');

        if ($purchaseRequest->items->count() > 2) {
            $itemSummary .= __('messages.notification_more_items', [
                'count' => $purchaseRequest->items->count() - 2,
            ]);
        }

        $details = collect([
            $purchaseRequest->purpose ? ['label' => __('ui.notification_details.purpose'), 'value' => $purchaseRequest->purpose] : null,
            $itemSummary !== '' ? ['label' => __('ui.notification_details.items'), 'value' => $itemSummary] : null,
            $purchaseRequest->required_date ? ['label' => __('ui.notification_details.required_date'), 'value' => $purchaseRequest->required_date->format('d/m/Y')] : null,
        ])->filter()->values()->all();

        return [
            'request_id' => $workflowRequest->id,
            'purchase_request_id' => $purchaseRequest->id,
            'request_code' => $workflowRequest->request_code,
            'status' => $workflowRequest->status,
            'requester_id' => $workflowRequest->creator?->id,
            'requester_name' => $workflowRequest->creator?->name ?? __('ui.unknown_user'),
            'fulfillment_route' => $purchaseRequest->fulfillment_route instanceof PurchaseRequestFulfillmentRoute
                ? $purchaseRequest->fulfillment_route->value
                : (string) $purchaseRequest->fulfillment_route,
            'details' => $details,
        ];
    }

    private function dynamicRequestDetails(WorkflowRequest $workflowRequest): array
    {
        $valuesByFieldId = $workflowRequest->values->keyBy('form_field_id');

        return $workflowRequest->formTemplate?->fields
            ?->filter(fn ($field) => $field->field_type !== 'file')
            ->map(function ($field) use ($valuesByFieldId): ?array {
                $requestValue = $valuesByFieldId->get($field->id);
                $value = $requestValue?->value;

                if ($value === null || $value === '') {
                    return null;
                }

                return [
                    'label' => $field->label,
                    'value' => Str::limit($this->valuePresenter->display($field, $value), 90),
                ];
            })
            ->filter()
            ->take(3)
            ->values()
            ->all() ?? [];
    }

    private function trimNumber(mixed $value): string
    {
        $normalized = rtrim(rtrim((string) $value, '0'), '.');

        return $normalized === '' ? '0' : $normalized;
    }
}
