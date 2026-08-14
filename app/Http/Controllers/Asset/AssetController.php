<?php

namespace App\Http\Controllers\Asset;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetUpdateRequest;
use App\Models\Asset;
use App\Services\Asset\AssetLifecycleService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private AssetLifecycleService $assetLifecycleService
    ) {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Asset::class);

        $query = Asset::query()
            ->with(['item.category', 'warehouse', 'activeAssignment.assignee'])
            ->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('asset_code', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhereHas('item', fn ($itemQuery) => $itemQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        return view('assets.index', [
            'assets' => $query->paginate(15)->withQueryString(),
            'statuses' => AssetStatus::cases(),
        ]);
    }

    public function show(Asset $asset): View
    {
        Gate::authorize('view', $asset);

        $asset->load([
            'item.category',
            'warehouse',
            'sourceReceiptItem.goodsReceipt.purchaseOrder.supplier',
            'assignments.assignee',
            'assignments.assigner',
            'assignments.sourceWarehouse',
            'assignments.returnRecord.receiver',
            'assignments.returnRecord.warehouse',
        ]);

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset): View
    {
        Gate::authorize('update', $asset);

        $asset->load(['item', 'warehouse']);

        return view('assets.edit', compact('asset'));
    }

    public function update(AssetUpdateRequest $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('update', $asset);

        $old = $asset->toArray();
        $asset->update($request->validated());

        $this->auditLogService->log('asset.updated', $asset, $old, $asset->fresh()->toArray());

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('assets.messages.asset_updated'));
    }

    public function releaseMaintenance(Request $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('completeMaintenance', $asset);

        $this->assetLifecycleService->releaseFromMaintenance($request->user(), $asset);

        return back()->with('success', __('assets.messages.maintenance_completed'));
    }
}
