{{-- filepath: resources/views/admin/stock-adjustment/edit.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Edit Stock Adjustment')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Stock Adjustment</h4>
        <h6>Update stock adjustment details</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">
            <i class="ti ti-list me-1"></i> Stock Adjustment List
        </a>
        <a href="{{ route('stock-adjustments.show', $stock_adjustment->id) }}" class="btn btn-primary">
            <i class="ti ti-eye me-1"></i> View
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('admin.stock-adjustment-form', ['stock_adjustment_id' => $stock_adjustment->id])
    </div>
</div>
@endsection