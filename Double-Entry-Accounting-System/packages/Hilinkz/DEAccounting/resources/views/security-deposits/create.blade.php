@extends('layouts.app-admin')

@section('title', 'New Security Deposits')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>New Security Deposit</h4>
            <h6>Record new security deposit</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-security-deposit.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>List of Security Deposits
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('de-accounting::new-security-deposit')
    </div>
</div>
@endsection