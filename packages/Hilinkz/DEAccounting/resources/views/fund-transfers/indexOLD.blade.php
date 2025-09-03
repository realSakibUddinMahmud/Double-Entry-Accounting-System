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
                <h3 class="card-title">Fund Transfers</h3>
                <div class="card-tools">
                    <a href="{{ route('de-fund-transfer.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New
                    </a>
                </div>

            </div>

            <div class="card-body">
                @livewire('de-accounting::journal-search-component')
                <hr>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Belong To</th>
                            <th>Account From</th>
                            <th>Account To</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($journals as $index => $journal)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($journal->date)) }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ class_basename($journal->journalable_alias) }} -
                                        {{ $journal->journalable->name ?? ($journal->journalable->title ?? 'N/A') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Title: {{ $journal->creditTransaction->account->title ?? 'N/A' }}<br>
                                        No: {{ $journal->creditTransaction->account->account_no ?? 'N/A' }}<br>
                                        Holder:
                                        {{ class_basename($journal->creditTransaction->account->accountable_alias ?? null) }}
                                        -
                                        {{ $journal->creditTransaction->account->accountable->name ?? ($journal->creditTransaction->account->accountable->title ?? 'N/A') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Title: {{ $journal->debitTransaction->account->title ?? 'N/A' }}<br>
                                        No: {{ $journal->debitTransaction->account->account_no ?? 'N/A' }}<br>
                                        Holder:
                                        {{ class_basename($journal->debitTransaction->account->accountable_alias ?? null) }}
                                        -
                                        {{ $journal->debitTransaction->account->accountable->name ?? ($journal->debitTransaction->account->accountable->title ?? 'N/A') }}
                                    </small>
                                </td>

                                <td>{{ $journal->amount }}</td>
                                <td>{{ $journal->note ?? 'N/A' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#confirmDeleteModal" data-id="{{ $journal->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @if ($journal->files && $journal->files->count() > 0)
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                            data-target="#filesModal{{ $journal->id }}">
                                            <i class="fas fa-paperclip"></i> Files
                                        </button>
                                    @endif

                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    {{ $journals->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
        @include('de-accounting::fund-transfers.delete-modal')
        @include('de-accounting::fund-transfers.files-modal')
    </div>
@endsection
