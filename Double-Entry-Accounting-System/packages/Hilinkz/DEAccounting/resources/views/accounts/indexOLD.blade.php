@extends('admin.layouts.new_admin')

@section('impersonate_leave')
    @include('admin.layouts.impersonate-leave')
@endsection

@section('custom_style')
    @include('styles.data-table')
    @include('styles.general')
@endsection

@section('content')
    <div class="content-wrapper">
        <br>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chart of Accounts</h3>
                <div class="card-tools">
                    <a href="{{ route('de-account.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create New Account
                    </a>
                </div>
            </div>
            <div class="card-body">
                @livewire('de-accounting::account-search-component')
                <hr>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Account No</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Additional Info</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $index => $account)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $account->account_no ?? null }}</td>
                                <td>{{ $account->title ?? null }}</td>
                                <td>{{ $account->accountType->title ?? null }}</td>
                                <td>
                                    <small class="text-muted">
                                        Belong To: {{ $account->accountable_alias ?? null }} -
                                        {{ $account->accountable->name ?? ($account->accountable->title ?? 'N/A') }}
                                    </small>
                                    @if ($account->accountType && $account->accountType->title == 'Bank' && $account->bankAccount)
                                        <br>
                                        <small class="text-muted">
                                            Bank: {{ $account->bankAccount->bank->bank_name ?? 'N/A' }}<br>
                                            A/C No: {{ $account->bankAccount->account_no ?? 'N/A' }}<br>
                                            Holder: {{ $account->bankAccount->account_name ?? 'N/A' }}<br>
                                            Branch: {{ $account->bankAccount->branch ?? 'N/A' }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $account->status }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success load-balance-btn"
                                        data-id="{{ $account->id }}"
                                        data-url="{{ route('de-account.latest-balance', ['id' => $account->id]) }}"
                                        id="balance-btn-{{ $account->id }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                                <td style="text-align: center">
                                    <a href="{{ route('de-account.edit', $account) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#confirmDeleteModal" data-id="{{ $account->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $accounts->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

        @include('de-accounting::accounts.delete-modal')
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.load-balance-btn');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const buttonEl = this;
                    const originalHTML = buttonEl.innerHTML;

                    buttonEl.disabled = true;
                    buttonEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            // Use balance as plain text, without parsing or formatting
                            const originalBalance = data.balance;
                            buttonEl.outerHTML =
                                `<button class="btn btn-sm btn-success load-balance-btn">${originalBalance}</button>`;
                        })
                        .catch(error => {
                            console.error(error);
                            buttonEl.innerHTML = 'Error';
                            setTimeout(() => {
                                buttonEl.innerHTML = originalHTML;
                                buttonEl.disabled = false;
                            }, 2000);
                        });
                });
            });
        });
    </script>
@endpush
