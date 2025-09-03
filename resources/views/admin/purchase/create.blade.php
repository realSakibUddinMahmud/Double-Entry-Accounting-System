{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/purchase/create.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Add Purchase')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Add Purchase</h4>
        <h6>Create a new purchase</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('purchases.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Purchase List
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        @livewire('admin.purchase-form')
    </div>
</div>
@endsection