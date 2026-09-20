<?php

namespace App\Services\Workflow;

use App\Contracts\Workflow\WorkflowApprovalRoutingHandler;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;

class WorkflowApprovalRoutingDispatcher
{
    public function __construct(private iterable $handlers) {}

    public function shouldCompleteAfterApproval(WorkflowRequest $workflowRequest, WorkflowStep $approvedStep): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof WorkflowApprovalRoutingHandler && $handler->supports($workflowRequest)) {
                return $handler->shouldCompleteAfterApproval($workflowRequest, $approvedStep);
            }
        }

        return false;
    }
}
