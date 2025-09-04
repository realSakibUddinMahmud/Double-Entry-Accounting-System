{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/sale/edit.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Edit Sale')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Sale</h4>
        <h6>Update sale details</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('sales.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Sales List
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <livewire:admin.sale-form :saleId="$sale->id" :mode="'edit'" />
    </div>
</div>
@endsection