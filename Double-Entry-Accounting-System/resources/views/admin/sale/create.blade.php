{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/sale/create.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Add Sale')

@can('sale-create')
    @section('content')
    <div class="page-header">
        <div class="page-title">
            <h4>Add Sale</h4>
            <h6>Create a new sale</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('sales.index') }}" class="btn btn-primary">
                <i class="ti ti-list me-1"></i>Sales List
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @livewire('admin.sale-form')
        </div>
    </div>
    @endsection
@else
@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Access Denied</h4>
        <h6>You don't have permission to create sales</h6>
    </div>
</div>
@endsection
@endcan
