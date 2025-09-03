{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/sale/payment/create.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Add Payment')

@section('content')
<div class="page-header">
    <h4>Add Payment for Invoice #{{ $sale->u_id ?? $sale->id }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.payments', $sale->id) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left"></i> Back to Payments
        </a>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-list"></i> Back to Sales List
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
                <strong>Total Received:</strong> {{ number_format($sale->paid_amount, 2) }}
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
        <form action="{{ route('sales.payments.store', $sale->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        name="amount"
                        id="amount"
                        class="form-control"
                        required
                        max="{{ $sale->due_amount }}"
                        value="{{ old('amount', $sale->due_amount) }}"
                    >
                    @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                    <small class="text-muted">Max: {{ number_format($sale->due_amount, 2) }}</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control" required value="{{ old('payment_date', date('Y-m-d')) }}">
                    @error('payment_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="received_in_account" class="form-label">Received In (Asset Account) <span class="text-danger">*</span></label>
                    <select name="received_in_account" id="received_in_account" class="form-control" required>
                        <option value="">Select Account</option>
                        @foreach($assetsAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->title }}
                                <span class="text-muted" style="font-size: 90%;">
                                    ({{ $account->accountable_alias ?? '-' }}:
                                    {{ $account->accountable->name ?? ($account->accountable->title ?? 'N/A') }})
                                </span>
                            </option>
                        @endforeach
                    </select>
                    @error('received_in_account') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="note" class="form-label">Note</label>
                <textarea name="note" id="note" class="form-control" rows="2"></textarea>
                @error('note') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3">
                <label for="attachments" class="form-label">Attachments</label>
                <input type="file"
                       name="attachments[]"
                       id="attachments"
                       class="form-control"
                       accept=".jpg,.jpeg,.png,.pdf"
                       multiple>
                @error('attachments') <span class="text-danger">{{ $message }}</span> @enderror
                @error('attachments.*') <span class="text-danger">{{ $message }}</span> @enderror
                <small class="text-muted">Allowed types: jpg, jpeg, png, pdf. You can select multiple files.</small>
            </div>
            @if(!$customerReceivableAccount)
                <div class="alert alert-warning">
                    Customer Receivable account not found for this customer. You must create a customer receivable account before recording a payment.
                </div>
                <a href="{{ route('de-account.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Create Receivable Account
                </a>
            @else
                <button type="submit" class="btn btn-success">Add Payment</button>
            @endif
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('amount');
    const max = parseFloat(amountInput.max);
    amountInput.addEventListener('input', function () {
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
    });
});
</script>
@endsection