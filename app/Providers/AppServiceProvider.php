<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Attachment;
use App\Models\PurchaseRequest;
use App\Models\WorkflowRequest;
use App\Policies\AssetPolicy;
use App\Policies\AttachmentPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\WorkflowRequestPolicy;
use App\Services\Procurement\PurchaseRequestWorkflowHandler;
use App\Services\Workflow\WorkflowTransitionDispatcher;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->tag([PurchaseRequestWorkflowHandler::class], 'workflow.transition.handlers');

        $this->app->singleton(
            WorkflowTransitionDispatcher::class,
            fn ($app) => new WorkflowTransitionDispatcher(
                $app->tagged('workflow.transition.handlers')
            )
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(WorkflowRequest::class, WorkflowRequestPolicy::class);

        Paginator::useBootstrapFive();
    }
}
