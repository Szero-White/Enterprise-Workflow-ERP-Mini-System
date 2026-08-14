<?php

namespace App\Services\Workflow;

use App\Contracts\Workflow\WorkflowTransitionHandler;
use App\Models\WorkflowRequest;

class WorkflowTransitionDispatcher
{
    public function __construct(private iterable $handlers)
    {
    }

    public function dispatch(WorkflowRequest $workflowRequest): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof WorkflowTransitionHandler && $handler->supports($workflowRequest)) {
                $handler->handle($workflowRequest);
            }
        }
    }
}
