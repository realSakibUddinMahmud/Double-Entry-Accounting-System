@extends('layouts.app-admin')

@section('title', 'New Payments')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>New Payment</h4>
            <h6>Record new payment transaction</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-payment.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>List of Payments
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('de-accounting::new-payment')
    </div>
</div>
@endsection