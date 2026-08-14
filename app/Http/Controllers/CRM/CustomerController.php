<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = Customer::query()->withCount('salesOrders')->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        return view('crm.customers.index', ['customers' => $query->paginate(12)->withQueryString()]);
    }

    public function create(): View
    {
        return view('crm.customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $customer = Customer::create($data);
        $this->auditLogService->log('customer.created', $customer, null, $customer->toArray());

        return redirect()->route('crm.customers.index')->with('success', __('crm.messages.created'));
    }

    public function edit(Customer $customer): View
    {
        return view('crm.customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $old = $customer->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $customer->update($data);
        $this->auditLogService->log('customer.updated', $customer, $old, $customer->fresh()->toArray());

        return redirect()->route('crm.customers.index')->with('success', __('crm.messages.updated'));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->salesOrders()->exists()) {
            return back()->with('error', __('crm.messages.delete_blocked'));
        }

        $old = $customer->toArray();
        $this->auditLogService->log('customer.deleted', $customer, $old, null);
        $customer->delete();

        return back()->with('success', __('crm.messages.deleted'));
    }
}
