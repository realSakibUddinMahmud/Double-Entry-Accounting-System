@extends('layouts.app-admin')

@section('title', 'New Income/Revenue')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>New Income / Revenue</h4>
            <h6>Record new income or revenue transaction</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-income-revenue.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>List of Income/Revenue
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('de-accounting::new-income-revenue')
    </div>
</div>
@endsection