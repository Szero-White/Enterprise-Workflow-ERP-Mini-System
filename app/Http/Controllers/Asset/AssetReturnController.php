<?php

namespace App\Http\Controllers\Asset;

use App\Enums\AssetCondition;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetReturnStoreRequest;
use App\Models\AssetAssignment;
use App\Models\Warehouse;
use App\Services\Asset\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssetReturnController extends Controller
{
    public function __construct(private AssetLifecycleService $assetLifecycleService)
    {
    }

    public function create(AssetAssignment $assignment): View
    {
        $assignment->load(['asset.item', 'assignee', 'returnRecord']);
        Gate::authorize('receiveReturn', $assignment->asset);
        abort_if($assignment->returnRecord, 422, __('assets.messages.assignment_already_returned'));

        return view('assets.returns.create', [
            'assignment' => $assignment,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'conditions' => [AssetCondition::Good, AssetCondition::NeedsMaintenance],
        ]);
    }

    public function store(AssetReturnStoreRequest $request, AssetAssignment $assignment): RedirectResponse
    {
        $assignment->loadMissing('asset');
        Gate::authorize('receiveReturn', $assignment->asset);

        $assetId = $assignment->asset_id;
        $this->assetLifecycleService->returnAsset($request->user(), $assignment, $request->validated());

        return redirect()
            ->route('assets.show', $assetId)
            ->with('success', __('assets.messages.asset_returned'));
    }
}
