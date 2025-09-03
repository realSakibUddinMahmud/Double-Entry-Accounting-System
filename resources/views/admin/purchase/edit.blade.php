{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/purchase/edit.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Edit Purchase')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Purchase</h4>
        <h6>Update purchase details</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('purchases.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Purchase List
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <livewire:admin.purchase-form :purchaseId="$purchase->id" :mode="'edit'" />
    </div>
</div>


@endsection