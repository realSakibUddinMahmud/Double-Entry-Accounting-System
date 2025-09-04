@extends('layouts.app-admin')

@section('title', 'Ledgers')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Ledgers</h4>
            <h6>View account ledger details</h6>
        </div>
    </div>
</div>

<div class="card card-body">
    @livewire('de-accounting::de-ledger-search')
</div>

<div class="card">
    @if(isset($haveData) && $haveData == true)
        <div class="card-body">
            @include('de-accounting::ledgers.data-table-new')
        </div>
    @else
        <div class="card-body">
            @if(isset($message))
                <div class="alert alert-danger text-center">{{ $message }}</div>
            @endif
            <div class="text-center py-5">
                <p class="text-muted mt-3">No ledger data available</p>
            </div>
        </div>
    @endif
</div>
@endsection