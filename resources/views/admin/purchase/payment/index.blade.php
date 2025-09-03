@extends('layouts.app-admin')
@section('title', 'Purchase Payments')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>
            Payments for Invoice #{{ $purchase->u_id ?? $purchase->id }}
            <span class="badge 
                @if($purchase->payment_status == 'Paid') bg-success
                @elseif($purchase->payment_status == 'Partial') bg-warning text-dark
                @else bg-danger
                @endif
                ms-2">
                {{ $purchase->payment_status }}
            </span>
        </h4>
        <h6>Supplier: {{ optional($purchase->supplier)->name ?? '-' }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-list"></i> Back to Purchase List
        </a>
        <a href="{{ route('purchases.payments.create', $purchase->id) }}" class="btn btn-success">
            <i class="ti ti-plus"></i> New Payment
        </a>
    </div>
</div>


<div class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <div class="alert alert-info mb-2">
                <strong>Supplier Payable:</strong> {{ number_format($purchase->total_amount-$purchase->shipping_cost, 2) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="alert alert-primary mb-2">
                <strong>Transportation Payable:</strong> {{ number_format($purchase->shipping_cost, 2) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="alert alert-success mb-2">
                <strong>Total Paid:</strong> {{ number_format($purchase->paid_amount, 2) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="alert alert-danger mb-2">
                <strong>Total Due:</strong> {{ number_format($purchase->due_amount, 2) }}
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
                        <th class="text-left">Paid From</th>
                        <th class="text-left">Paid To</th>
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
                                    {{ $journal->creditTransaction->account->title ?? 'N/A' }}<br>
                                    @if (!empty($journal->creditTransaction->account->account_no))
                                        No: {{ $journal->creditTransaction->account->account_no }}<br>
                                    @endif
                                    {{ class_basename($journal->creditTransaction->account->accountable_alias ?? null) }}
                                    -
                                    {{ $journal->creditTransaction->account->accountable->name ?? ($journal->creditTransaction->account->accountable->title ?? 'N/A') }}
                                </p>
                            </td>
                            <td class="text-left">
                                <p class="text-left">
                                    Title:
                                    {{ $journal->debitTransaction->account->title ?? 'N/A' }}<br>
                                    @if (!empty($journal->debitTransaction->account->account_no))
                                        No: {{ $journal->debitTransaction->account->account_no }}<br>
                                    @endif
                                    {{ class_basename($journal->debitTransaction->account->accountable_alias ?? null) }}
                                    -
                                    {{ $journal->debitTransaction->account->accountable->name ?? ($journal->creditTransaction->account->accountable->title ?? 'N/A') }}
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
                                @include('admin.purchase.payment.journal-modals', ['journal' => $journal])
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