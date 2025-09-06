@extends('layouts.app-admin')
@section('title', 'Sale Payments')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>
            Payments for Invoice #{{ $sale->u_id ?? $sale->id }}
            <span class="badge 
                @if($sale->payment_status == 'Paid') bg-success
                @elseif($sale->payment_status == 'Partial') bg-warning text-dark
                @else bg-danger
                @endif
                ms-2">
                {{ $sale->payment_status }}
            </span>
        </h4>
        <h6>Customer: {{ optional($sale->customer)->name ?? '-' }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-list"></i> Back to Sale List
        </a>
        <a href="{{ route('sales.payments.create', $sale->id) }}" class="btn btn-success">
            <i class="ti ti-plus"></i> New Payment
        </a>
    </div>
</div>

<div class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <div class="alert alert-info mb-2">
                <strong>Total Receivable:</strong> {{ number_format($sale->total_amount, 2) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success mb-2">
                <strong>Total Paid:</strong> {{ number_format($sale->paid_amount, 2) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-danger mb-2">
                <strong>Total Due:</strong> {{ number_format($sale->due_amount, 2) }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Received Account</th>
                        <th class="text-left">Note</th>
                        <th class="text-right">Amount</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $i => $journal)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($journal->payment_date)->format('d/m/Y') }}</td>
                            <td class="text-left">
                                <p class="text-left">
                                    Title:
                                    {{ $journal->debitTransaction->account->title ?? 'N/A' }}<br>
                                    @if (!empty($journal->debitTransaction->account->account_no))
                                        No: {{ $journal->debitTransaction->account->account_no }}<br>
                                    @endif
                                    {{ class_basename($journal->debitTransaction->account->accountable_alias ?? null) }}
                                    -
                                    {{ $journal->debitTransaction->account->accountable->name ?? ($journal->debitTransaction->account->accountable->title ?? 'N/A') }}
                                </p>
                            </td>
                            <td class="text-left">{{ $journal->note }}</td>
                            <td class="text-right">{{ number_format($journal->amount, 2) }}</td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    <!-- View Modal Trigger -->
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-journal-{{ $journal->id }}">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    <!-- Delete Modal Trigger -->
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-journal-{{ $journal->id }}">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                </div>
                                {{-- Include modals --}}
                                @include('admin.sale.payment.journal-modals', ['journal' => $journal])
                            </td>
                        </tr>
                    @empty
                        {{-- Note:Nothing will be add here --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection