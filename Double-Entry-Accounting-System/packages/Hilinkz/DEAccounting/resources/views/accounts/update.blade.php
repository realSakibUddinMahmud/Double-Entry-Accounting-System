{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/packages/Hilinkz/DEAccounting/resources/views/accounts/update.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Edit Account')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Edit Account</h4>
            <h6>Update account details</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-account.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Chart of Accounts
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <livewire:de-accounting::edit-account :accountId="$account_id" />
    </div>
</div>
@endsection