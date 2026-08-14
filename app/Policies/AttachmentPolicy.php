<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function download(User $user, Attachment $attachment): bool
    {
        $workflowRequest = $attachment->workflowRequest;

        if (! $workflowRequest) {
            return false;
        }

        return $user->can('view', $workflowRequest)
            || $user->can('review', $workflowRequest);
    }
}
