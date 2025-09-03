@extends('layouts.app-admin')
@section('title', 'Sales')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>All Sales</h4>
            <h6>Manage your Sales</h6>
        </div>
    </div>
    @can('sale-create')
    <div class="page-btn">
        <a href="{{ route('sales.create') }}" class="btn btn-primary" title="Add New Sale">
            <i class="ti ti-circle-plus me-1"></i>Add Sale
        </a>
    </div>
    @endcan
</div>
<!-- /sales list -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Search & Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search Invoice ID</label>
                <input type="text" name="search" class="form-control" placeholder="Search by invoice ID (u_id)..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Store</label>
                <select name="store_id" class="form-select">
                    <option value="">All Stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="">All Status</option>
                    <option value="Paid" {{ request('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Partial" {{ request('payment_status') == 'Partial' ? 'selected' : '' }}>Partial</option>
                    <option value="Pending" {{ request('payment_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-6">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To">
                    </div>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Search
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="ti ti-refresh me-1"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Sales List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $sales->total() }} sales</span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Invoice ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Store</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Payment Status</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $sale->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ $sale->u_id ?? $sale->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                            <td>{{ optional($sale->customer)->name ?? '-' }}</td>
                            <td>{{ optional($sale->store)->name ?? '-' }}</td>
                            <td>{{ number_format($sale->total_amount, 2) }}</td>
                            <td>{{ number_format($sale->paid_amount, 2) }}</td>
                            <td>{{ number_format($sale->due_amount, 2) }}</td>
                            <td>
                                @if($sale->payment_status == 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($sale->payment_status == 'Partial')
                                    <span class="badge bg-warning">Partial</span>
                                @else
                                    <span class="badge bg-danger">Pending</span>
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action d-flex align-items-center">
                                    @can('sale-show')
                                    <a class="me-2 p-2"
                                       href="{{ route('sales.show', $sale->id) }}"
                                       title="View Invoice">
                                        <i class="ti ti-file-invoice"></i>
                                    </a>
                                    @endcan
                                    @can('sale-edit')
                                    <a class="me-2 p-2"
                                       href="{{ route('sales.edit', $sale->id) }}"
                                       title="Edit Sale">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('sale-payment-view')
                                    <a class="me-2 p-2"
                                       href="{{ route('sales.payments', $sale->id) }}"
                                       title="Manage Payments">
                                        <i class="ti ti-currency-dollar"></i>
                                    </a>
                                    @endcan
                                    @can('sale-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#deleteSaleModal"
                                       data-id="{{ $sale->id }}"
                                       title="Delete Sale">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- No sales found --}}
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <x-admin.pagination :paginator="$sales" info-text="sales" />
    </div>
</div>
<!-- /sales list -->

@include('admin.sale.delete-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete Modal
    var deleteModal = document.getElementById('deleteSaleModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteSaleForm').action = "{{ url('sales') }}/" + id;
        });
    }
});
</script>
@endpush





