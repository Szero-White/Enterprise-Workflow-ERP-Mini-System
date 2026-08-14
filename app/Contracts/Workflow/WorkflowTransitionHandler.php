<?php

namespace App\Contracts\Workflow;

use App\Models\WorkflowRequest;

interface WorkflowTransitionHandler
{
    public function supports(WorkflowRequest $workflowRequest): bool;

    public function handle(WorkflowRequest $workflowRequest): void;
}
