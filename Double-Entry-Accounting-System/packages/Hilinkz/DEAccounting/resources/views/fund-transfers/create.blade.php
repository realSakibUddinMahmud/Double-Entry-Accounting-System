@extends('layouts.app-admin')

@section('title', 'New Fund Transfer')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>New Fund Transfer</h4>
            <h6>Transfer funds between accounts</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-fund-transfer.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>List of Fund Transfers
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @livewire('de-accounting::new-fund-transfer')
    </div>
</div>
@endsection