<?php

namespace App\Http\Controllers\Asset;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetAssignmentStoreRequest;
use App\Models\Asset;
use App\Models\User;
use App\Services\Asset\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssetAssignmentController extends Controller
{
    public function __construct(private AssetLifecycleService $assetLifecycleService)
    {
    }

    public function create(Asset $asset): View
    {
        Gate::authorize('assign', $asset);

        $asset->load(['item', 'warehouse']);
        abort_unless($asset->status === AssetStatus::Available && $asset->warehouse_id, 422, __('assets.messages.asset_not_available'));

        return view('assets.assignments.create', [
            'asset' => $asset,
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(AssetAssignmentStoreRequest $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('assign', $asset);

        $this->assetLifecycleService->assign($request->user(), $asset, $request->validated());

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('assets.messages.asset_assigned'));
    }
}
