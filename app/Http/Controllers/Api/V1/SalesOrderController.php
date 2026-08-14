<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SalesOrderResource;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SalesOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = SalesOrderStatus::tryFrom((string) $request->input('status'));

        $orders = SalesOrder::query()
            ->with(['customer', 'warehouse'])
            ->withCount('items')
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = trim((string) $request->input('q'));
                $query->where(fn ($builder) => $builder
                    ->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")));
            })
            ->latest('order_date')
            ->latest('id')
            ->paginate($this->perPage($request));

        return SalesOrderResource::collection($orders);
    }

    public function show(SalesOrder $order): SalesOrderResource
    {
        return new SalesOrderResource(
            $order->load(['customer', 'warehouse', 'items'])->loadCount('items')
        );
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
