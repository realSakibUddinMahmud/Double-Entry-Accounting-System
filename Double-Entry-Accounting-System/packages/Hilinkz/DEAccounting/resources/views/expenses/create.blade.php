@extends('layouts.app-admin')

@section('title', 'New Expenses')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>New Expense</h4>
            <h6>Record new expense transaction</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-expense.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>List of Expenses
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('de-accounting::new-expense')
    </div>
</div>
@endsection