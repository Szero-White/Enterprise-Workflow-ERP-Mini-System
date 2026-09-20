<?php

namespace App\Contracts\Workflow;

use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;

interface WorkflowApprovalRoutingHandler
{
    public function supports(WorkflowRequest $workflowRequest): bool;

    public function shouldCompleteAfterApproval(WorkflowRequest $workflowRequest, WorkflowStep $approvedStep): bool;
}
