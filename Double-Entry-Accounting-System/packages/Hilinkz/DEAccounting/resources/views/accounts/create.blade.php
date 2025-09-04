{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/packages/Hilinkz/DEAccounting/resources/views/accounts/create.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Create Account')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Create Account</h4>
            <h6>Add a new account to your chart of accounts</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-account.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Chart of Accounts
        </a>
    </div>
</div>

<div>
    <livewire:de-accounting::create-account />
</div>
@endsection