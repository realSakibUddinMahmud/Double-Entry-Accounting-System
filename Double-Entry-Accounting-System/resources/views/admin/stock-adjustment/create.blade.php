{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/stock-adjustment/create.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Add Stock Adjustment')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Add Stock Adjustment</h4>
        <h6>Create a new stock adjustment entry</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i> Stock Adjustment List
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('admin.stock-adjustment-form')
    </div>
</div>
@endsection