@extends('layouts.app-admin')

@section('title', 'New Loan/Investments')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>New Loan / Investment</h4>
            <h6>Record new loan or investment transaction</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-loan-investment.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>List of Loan / Investments
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('de-accounting::new-loan-investment')
    </div>
</div>
@endsection