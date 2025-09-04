{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/purchase/payment/create.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Add Payment')

@section('content')
<div class="page-header">
    <h4>Add Payment for Invoice #{{ $purchase->u_id ?? $purchase->id }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.payments', $purchase->id) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left"></i> Back to Payments
        </a>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-list"></i> Back to Purchase List
        </a>
    </div>
</div>

<div class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <div class="alert alert-info mb-2">
                <strong>Supplier Payable:</strong> {{ number_format(($purchase->total_amount - $purchase->shipping_cost), 2) }}
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
        <form action="{{ route('purchases.payments.store', $purchase->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="pay_to" class="form-label">Pay To <span class="text-danger">*</span></label>
                    <select name="pay_to" id="pay_to" class="form-control" required>
                        <option value="">Select Payee</option>
                        <option value="supplier">Supplier</option>
                        <option value="transportation">Transportation</option>
                    </select>
                    @error('pay_to') <span class="text-danger">{{ $message }}</span> @enderror
                    <small class="text-muted"> Whom you want to pay?</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        name="amount"
                        id="amount"
                        class="form-control"
                        required
                        max="{{ $purchase->due_amount }}"
                        value="{{ old('amount', $purchase->due_amount) }}"
                    >
                    @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                    <small class="text-muted" id="amount-help">Max: {{ number_format($purchase->due_amount, 2) }}</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control" required value="{{ old('payment_date', date('Y-m-d')) }}">
                    @error('payment_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="paid_from_account" class="form-label">Paid From (Asset Account) <span class="text-danger">*</span></label>
                    <select name="paid_from_account" id="paid_from_account" class="form-control" required>
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
                    @error('paid_from_account') <span class="text-danger">{{ $message }}</span> @enderror
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
            @if(!$payableAccount)
                <div class="alert alert-warning">
                    Payable account not found for this supplier. You must create a payable account before making a payment.
                </div>
                <a href="{{ route('de-account.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Create Payable Account
                </a>
            @else
                <button type="submit" class="btn btn-success">Add Payment</button>
            @endif
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const payToSelect = document.getElementById('pay_to');
    const amountInput = document.getElementById('amount');
    const amountHelp = document.getElementById('amount-help');
    
    const supplierPayable = {{ ($purchase->total_amount - $purchase->shipping_cost) }};
    const transportationPayable = {{ $purchase->shipping_cost }};
    const totalDue = {{ $purchase->due_amount }};
    
    payToSelect.addEventListener('change', function() {
        if (this.value === 'supplier') {
            const maxAmount = Math.min(supplierPayable, totalDue);
            amountInput.max = maxAmount;
            amountInput.value = maxAmount;
            amountHelp.textContent = `Max: ${maxAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (Supplier Payable)`;
        } else if (this.value === 'transportation') {
            const maxAmount = Math.min(transportationPayable, totalDue);
            amountInput.max = maxAmount;
            amountInput.value = maxAmount;
            amountHelp.textContent = `Max: ${maxAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (Transportation Payable)`;
        } else {
            amountInput.max = totalDue;
            amountInput.value = totalDue;
            amountHelp.textContent = `Max: ${totalDue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
    });

    amountInput.addEventListener('input', function () {
        const max = parseFloat(this.max);
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
    });
});
</script>
@endsection