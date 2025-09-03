{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/packages/Hilinkz/DEAccounting/resources/views/journals/index.blade.php --}}
@extends('layouts.app-admin')

@section('title', 'Journals')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Journals</h4>
            <h6>Your journal entries</h6>
        </div>
    </div>
    {{-- Temporarily removed refresh button
    <div class="page-btn">
        <a href="{{ route('de-journal.index') }}" class="btn btn-primary">
            <i class="ti ti-refresh me-1"></i>Refresh
        </a>
    </div>
    --}}
</div>

<div class="card card-body">
    @livewire('de-accounting::de-journal-search')
</div>

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            @include('de-accounting::journals.journals-data-table')
        </div>
    </div>
</div>
@endsection