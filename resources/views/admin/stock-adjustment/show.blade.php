{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/stock-adjustment/show.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Stock Adjustment Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Stock Adjustment Details</h4>
        <h6>View stock adjustment information</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">
            <i class="ti ti-list me-1"></i> Stock Adjustment List
        </a>
        <a href="{{ route('stock-adjustments.edit', $stock_adjustment->id) }}" class="btn btn-warning">
            <i class="ti ti-edit me-1"></i> Edit
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($stock_adjustment->date)->format('d/m/Y') }}
            </div>
            <div class="col-md-4">
                <strong>Store:</strong> {{ optional($stock_adjustment->store)->name ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>User:</strong> {{ optional($stock_adjustment->user)->name ?? '-' }}
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <strong>Note:</strong> {{ $stock_adjustment->note ?? '-' }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Adjusted Products</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Base Unit</th>
                        <th>Action</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stock_adjustment->productStockAdjustments as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($item->product)->name ?? '-' }}</td>
                            <td>
                                {{ optional(optional($item->product)->productStores->where('store_id', $stock_adjustment->store_id)->first()->base_unit)->name
                                    ?? '-' }}
                            </td>
                            <td>
                                @if($item->action == '+')
                                    <span class="badge bg-success">Increase</span>
                                @else
                                    <span class="badge bg-danger">Decrease</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No products adjusted.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection