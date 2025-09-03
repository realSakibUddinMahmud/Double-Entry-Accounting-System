{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/packages/Hilinkz/DEAccounting/resources/views/fund-transfers/index.blade.php --}}
@extends('layouts.app-admin')

@section('title', 'Fund Transfers')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Fund Transfers</h4>
            <h6>Manage your fund transfers</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('de-fund-transfer.create') }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>New Fund Transfer
        </a>
    </div>
</div>

<div class="card card-body">
    @livewire('de-accounting::journal-search-component')
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Fund Transfer List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $journals->total() }} transfers</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Account From</th>
                        <th class="text-left">Account To</th>
                        <th class="text-left">Note</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($journals as $index => $journal)
                        <tr>
                            <td>{{ $journals->firstItem() + $index }}</td>
                            <td class="text-center">{{ date('d/m/Y', strtotime($journal->date)) }}</td>
                            <td>
                                <p class="text-left">
                                    Title: {{ $journal->creditTransaction->account->title ?? 'N/A' }}<br>
                                    @if (!empty($journal->creditTransaction->account->account_no))
                                        No: {{ $journal->creditTransaction->account->account_no }}<br>
                                    @endif
                                    {{ class_basename($journal->creditTransaction->account->accountable_alias ?? null) }}
                                    -
                                    {{ $journal->creditTransaction->account->accountable->name ?? ($journal->creditTransaction->account->accountable->title ?? 'N/A') }}
                                </p>
                            </td>
                            <td>
                                <p class="text-left">
                                    Title: {{ $journal->debitTransaction->account->title ?? 'N/A' }}<br>
                                    @if (!empty($journal->debitTransaction->account->account_no))
                                        No: {{ $journal->debitTransaction->account->account_no }}<br>
                                    @endif
                                    {{ class_basename($journal->debitTransaction->account->accountable_alias ?? null) }}
                                    -
                                    {{ $journal->debitTransaction->account->accountable->name ?? ($journal->debitTransaction->account->accountable->title ?? 'N/A') }}
                                </p>
                            </td>
                            <td class="text-left">{{ $journal->note ?? null }}</td>
                            <td class="text-right">{{ $journal->amount }}</td>
                            <td class="text-right">
                                @if ($journal->files && $journal->files->count() > 0)
                                    <button type="button" class="btn btn-sm btn-fa-paperclip"
                                        data-bs-toggle="modal"
                                        data-bs-target="#filesModal{{ $journal->id }}">
                                        <i class="fas fa-paperclip text-success" aria-hidden="true"></i>
                                    </button>
                                @endif
                                @can('de-fund-transfer-delete')
                                    <button type="button" class="btn btn-sm btn-far-fa-trash-alt"
                                        data-bs-toggle="modal" data-bs-target="#confirmDeleteModal"
                                        data-id="{{ $journal->id }}">
                                        <i class="far fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <x-admin.pagination :paginator="$journals" info-text="transfers" />
    </div>
</div>

@include('de-accounting::fund-transfers.delete-modal')
@include('de-accounting::fund-transfers.files-modal')
@endsection

