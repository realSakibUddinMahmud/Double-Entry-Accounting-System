@extends('layouts.app-admin')
@section('title', 'Chart of Accounts')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Chart of Accounts</h4>
            <h6>Manage your Chart of Accounts</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-account.create') }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>Add Account
        </a>
    </div>
</div>
<!-- /account list -->
<div class="card card-body">
    @livewire('de-accounting::account-search-component')
</div>
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Account List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $accounts->total() }} accounts</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Account No</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Parent</th>
                        <th>Additional Info</th>
                        <th>Status</th>
                        <th>Balance</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $index => $account)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $account->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ $account->account_no ?? '-' }}</td>
                            <td class="text-gray-9">{{ $account->title ?? '-' }}</td>
                            <td>
                                <span class="badge bg-primary" >{{ $account->accountType->title ?? 'N/A' }}</span>
                            </td>
                            <td>
                                {{ $account->parent ? $account->parent->title : '-' }}
                            </td>
                            <td>
                                <p>
                                    Belong To: {{ $account->accountable_alias ?? '-' }} -
                                    {{ $account->accountable->name ?? ($account->accountable->title ?? 'N/A') }}
                                </p>
                                @if ($account->accountType && $account->accountType->title == 'Bank' && $account->bankAccount)
                                    <p>
                                        Bank: {{ $account->bankAccount->bank->bank_name ?? 'N/A' }}<br>
                                        A/C No: {{ $account->bankAccount->account_no ?? 'N/A' }}<br>
                                        Holder: {{ $account->bankAccount->account_name ?? 'N/A' }}<br>
                                        Branch: {{ $account->bankAccount->branch ?? 'N/A' }}
                                    </p>
                                @endif
                            </td>
                            <td>
                                @if ($account->status == 'ACTIVE')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary load-balance-btn"
                                    data-id="{{ $account->id }}"
                                    data-url="{{ route('de-account.latest-balance', ['id' => $account->id]) }}"
                                    id="balance-btn-{{ $account->id }}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    <a class="me-2 p-2"
                                       href="{{ route('de-account.edit', $account) }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-account-modal"
                                       data-id="{{ $account->id }}">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Note:Nothing will be add here --}}
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <x-admin.pagination :paginator="$accounts" info-text="accounts" />
    </div>
</div>
<!-- /account list -->
@include('de-accounting::accounts.delete-modal')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.load-balance-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const buttonEl = this;
                    const originalHTML = buttonEl.innerHTML;
                    const originalClass = buttonEl.className;

                    // Show loading state (primary outline)
                    buttonEl.className = 'btn btn-sm btn-outline-primary';
                    buttonEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                    buttonEl.disabled = true;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            // Success state (primary with balance)
                            buttonEl.className = 'btn btn-sm btn-primary';
                            buttonEl.innerHTML = `<i class="fa fa-eye"></i> ${data.balance}`;
                            buttonEl.disabled = false;
                        })
                        .catch(error => {
                            console.error(error);
                            // Error state (red)
                            buttonEl.className = 'btn btn-sm btn-danger';
                            buttonEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error';
                            
                            setTimeout(() => {
                                // Revert to original (primary outline)
                                buttonEl.className = originalClass;
                                buttonEl.innerHTML = originalHTML;
                                buttonEl.disabled = false;
                            }, 2000);
                        });
                });
            });
        });
    </script>
@endpush
