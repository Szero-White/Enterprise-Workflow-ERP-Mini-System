<?php

namespace App\Http\Controllers\Sales;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderStoreRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct(private SalesOrderService $salesOrderService)
    {
    }

    public function index(Request $request): View
    {
        $query = SalesOrder::query()->with(['customer', 'warehouse', 'creator'])->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('order_code', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%")));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return view('sales.orders.index', [
            'orders' => $query->paginate(12)->withQueryString(),
            'statuses' => SalesOrderStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('sales.orders.create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(SalesOrderStoreRequest $request): RedirectResponse
    {
        $order = $this->salesOrderService->createDraft($request->user(), $request->validated());

        return redirect()->route('sales.orders.show', $order)->with('success', __('sales.messages.draft_created'));
    }

    public function show(SalesOrder $order): View
    {
        $order->load(['customer', 'warehouse', 'creator', 'items.product']);

        return view('sales.orders.show', compact('order'));
    }

    public function confirm(Request $request, SalesOrder $order): RedirectResponse
    {
        $this->salesOrderService->confirm($request->user(), $order);

        return redirect()->route('sales.orders.show', $order)->with('success', __('sales.messages.confirmed'));
    }

    public function cancel(Request $request, SalesOrder $order): RedirectResponse
    {
        $this->salesOrderService->cancel($request->user(), $order);

        return redirect()->route('sales.orders.show', $order)->with('success', __('sales.messages.cancelled'));
    }
}
